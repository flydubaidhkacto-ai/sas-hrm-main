<?php
declare(strict_types=1);

use App\Http\Controllers\RosterController;
use App\Models\{Attendance, InOutRecord, Roster, RosterShift, Shift, SuperDepartment, User};
use App\Services\WfhAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

// Check external selectors before boot, because providers can query during boot.
if (getenv('APP_ENV') !== 'testing'
    || !preg_match('/^hrm_wfh_test_[a-z0-9_]+$/', getenv('DB_DATABASE') ?: '')
    || !in_array(getenv('DB_HOST'), ['hrm-development-db', 'localhost', '127.0.0.1'], true)) {
    fwrite(STDERR, "Use APP_ENV=testing, a local DB_HOST, and a disposable hrm_wfh_test_* database.\n");
    exit(1);
}
foreach (['QUEUE_CONNECTION'=>'null','MAIL_MAILER'=>'array','CACHE_STORE'=>'array','SESSION_DRIVER'=>'array'] as $key=>$value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['mail.default' => 'array', 'queue.default' => 'null', 'cache.default' => 'array']);
$session = $app['session']->driver('array');
$session->start();
$app['session']->setDefaultDriver('array');
$app->instance('session.store', $session);
$results = [];
function check(string $name, bool $passed, array $evidence = []): void {
    global $results;
    $results[] = compact('name', 'passed', 'evidence');
    echo json_encode(end($results), JSON_UNESCAPED_SLASHES) . "\n";
    if (!$passed) throw new RuntimeException('Unexpected result: ' . $name);
}
function req(array $data = []): Request {
    global $app, $session;
    $request = Request::create('http://localhost:8000/roster/update/1', 'POST', $data);
    $request->headers->set('Accept', 'application/json');
    $request->setLaravelSession($session);
    $route = clone $app['router']->getRoutes()->getByName('roster.index');
    $route->bind($request);
    $request->setRouteResolver(fn() => $route);
    $app->instance('request', $request);
    return $request;
}
function payload(Roster $roster, array $employees, string $date, array $shifts, array $wfh = [], array $noDuty = []): array {
    return ['name'=>$roster->name, 'super_department_id'=>$roster->super_department_id,
        'data'=>[['uid'=>'row-test', 'employees'=>$employees, 'shifts'=>[$date=>$shifts]]],
        'editableDates'=>[$date], 'wfh_assignments'=>$wfh, 'no_shift_duty'=>['row-test'=>[$date=>($noDuty[$date]??'')]]];
}
function roster(string $name, array $employees, string $date, ?RosterShift $shift, array $wfh = [], bool $active = false): Roster {
    global $admin, $station;
    $r = Roster::create(['name'=>$name,'start'=>$date,'end'=>$date,'created_by'=>$admin->id,
        'super_department_id'=>$station->id,'is_active'=>$active,'no_shift_duty'=>[],'wfh_assignments'=>$wfh]);
    foreach ($employees as $id) $r->items()->create(['employee_id'=>$id,'roster_shift_id'=>$shift?->id,'date'=>$date,'group'=>0,'order'=>0]);
    return $r;
}
function shiftCells(string $html): array {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//tr[td[contains(., "Synthetic Alice")]]/td[contains(concat(" ", normalize-space(@class), " "), " table-col ")]');
    // Index puts employee names and date cells in separate side-by-side tables.
    if (!$nodes->length) $nodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " shift-name ")]');
    return array_map(fn($node)=>preg_replace('/\s+/', ' ', trim($node->textContent)), iterator_to_array($nodes));
}
$failed = false;
DB::beginTransaction();
try {
    check('disposable database has no configured devices', DB::table('zkteco_clouds')->count() === 0);
    $station = SuperDepartment::create(['name'=>'Synthetic Station']);
    $baseShift = Shift::create(['name'=>'Synthetic Base','start'=>'09:00:00','end'=>'17:00:00','break_time_minutes'=>30]);
    $admin = User::create(['name'=>'Synthetic Reviewer','email'=>'reviewer@example.invalid','role'=>'super_admin','is_active'=>true,'shift_id'=>$baseShift->id]);
    $alice = User::create(['name'=>'Synthetic Alice','email'=>'alice@example.invalid','role'=>'employee','is_active'=>true,'shift_id'=>$baseShift->id,'grace_minutes'=>15]);
    $bob = User::create(['name'=>'Synthetic Bob','email'=>'bob@example.invalid','role'=>'employee','is_active'=>true,'shift_id'=>$baseShift->id]);
    Auth::setUser($admin);
    req();
    $controller = $app->make(RosterController::class);
    $service = $app->make(WfhAttendanceService::class);
    $shift = RosterShift::create(['name'=>'Synthetic DAY','code'=>'SYN-D','start'=>'09:00:00','end'=>'17:00:00','display_only'=>false]);
    $date = '2026-11-01';

    // The no-shift UI path must never save a WFH label without attendance.
    $before = Roster::count();
    try {
        $controller->store(req(['name'=>'Synthetic No Shift WFH','super_department_id'=>$station->id,
            'data'=>[['employees'=>[$alice->id],'shifts'=>[$date=>[]]]], 'no_shift_duty'=>[[$date=>'WFH']]]));
        check('no-shift WFH create rejected', false);
    } catch (ValidationException $e) {
        check('no-shift WFH create rejected', isset($e->errors()["no_shift_duty.0.$date"]));
    }
    check('rejected create writes nothing', Roster::count()===$before);
    $legacy = roster('Synthetic Legacy',[$alice->id],$date,null);
    $legacy->update(['no_shift_duty'=>[[$date=>'WFH']]]);
    $session->forget('errors');
    $controller->publish_draft($legacy);
    check('legacy no-shift WFH publication blocked', !$legacy->fresh()->is_active && $session->has('errors'));
    check('legacy publication creates no attendance', Attendance::where('roster_id',$legacy->id)->doesntExist());
    try {
        $controller->update(req(payload($legacy,[$alice->id],$date,[],[],[$date=>'WFH'])),$legacy->id);
        check('no-shift WFH update rejected', false);
    } catch (ValidationException $e) {
        check('no-shift WFH update rejected', isset($e->errors()["no_shift_duty.row-test.$date"]));
    }
    $legacyHtml = view('roster.print',['roster'=>$legacy,'formatted'=>$legacy->formatted()])->render();
    check('legacy print explains missing shift without WFH duty label', shiftCells($legacyHtml)===['Select a shift for remote work']);

    // A real shifted WFH assignment works; this is the positive control.
    $sourceDate = '2026-11-03';
    $source = roster('Synthetic Copy Source',[$alice->id],$sourceDate,$shift,[$alice->id=>[$sourceDate=>'WFH']]);
    $controller->publish_draft($source);
    $source->refresh();
    $attendance = Attendance::where('roster_id',$source->id)->first();
    check('shifted WFH positive control creates eight hours without punches', $attendance && $attendance->working_time==='08:00:00' && $attendance->check_in===null && $attendance->check_out===null);
    foreach (['09:10:00','10:00:00'] as $time) {
        $punch = InOutRecord::create(['user_id'=>$alice->id,'status'=>'1','date_time'=>"$sourceDate $time",'device_uid'=>'synthetic-offline']);
        App\Actions\EmployeePunched\Execute::run($punch);
        $attendance->refresh();
        check("WFH full credit survives a single punch at $time with 15-minute grace", $attendance->working_time==='08:00:00' && $attendance->check_in===null && !$attendance->got_late_check_in && $attendance->late_fine_id===null);
    }

    // Copies clear WFH along with shifts; assigning on the new date must work.
    $controller->copy($source->id);
    $copy = Roster::where('name','Synthetic Copy Source Copy')->firstOrFail();
    $newDate = substr((string)$copy->start,0,10);
    check('copy advances date and clears WFH', $newDate==='2026-11-04' && $copy->wfh_assignments===[]);
    check('copy clears shifts and creates no attendance', $copy->items()->whereNotNull('roster_shift_id')->doesntExist() && Attendance::where('roster_id',$copy->id)->doesntExist());
    $response = $controller->update(req(payload($copy,[$alice->id],$newDate,[['id'=>$shift->id]],[$alice->id=>[$newDate=>'WFH']])),$copy->id);
    check('copied roster accepts a fresh WFH assignment on new date', $response->getStatusCode()===200);
    $copy->refresh();
    $session->forget('errors');
    $controller->publish_draft($copy);
    check('copied roster publishes on new date', $copy->fresh()->is_active && !$session->has('errors'));
    check('copy credits only new date with full shift', Attendance::where('roster_id',$copy->id)->whereDate('date',$newDate)->where('working_time','08:00:00')->count()===1);
    check('source attendance unchanged after copy', Attendance::where('roster_id',$source->id)->count()===1 && $attendance->fresh()->working_time==='08:00:00');
    $multi = roster('Synthetic Multi',[$bob->id],'2026-12-30',$shift,[$bob->id=>['2026-12-30'=>'WFH','2026-12-31'=>'WFH']]);
    $multi->update(['end'=>'2026-12-31']);
    $multi->items()->create(['employee_id'=>$bob->id,'roster_shift_id'=>$shift->id,'date'=>'2026-12-31','group'=>0,'order'=>0]);
    $controller->copy($multi->id);
    $multiCopy = Roster::where('name','Synthetic Multi Copy')->firstOrFail();
    check('multi-day copy preserves offsets across year boundary', $multiCopy->items()->orderBy('date')->pluck('date')->map(fn($d)=>substr($d,0,10))->all()===['2027-01-01','2027-01-02'] && $multiCopy->wfh_assignments===[]);

    // Finding 3: grouped row contains two employees but just Alice has WFH.
    $groupDate = '2026-11-05';
    $group = roster('Synthetic Group',[$alice->id,$bob->id],$groupDate,$shift,[$alice->id=>[$groupDate=>'WFH']]);
    $controller->publish_draft($group);
    $group->refresh();
    check('grouped WFH attendance positive control', Attendance::where('roster_id',$group->id)->where('duty_type','Work From Home')->count()===1);
    $singleHtml = view('roster.print',['roster'=>$source,'formatted'=>$source->formatted()])->render();
    $groupHtml = view('roster.print',['roster'=>$group,'formatted'=>$group->formatted()])->render();
    check('single-employee print control displays WFH', shiftCells($singleHtml)===['WFH']);
    $groupCell = implode(' ',shiftCells($groupHtml));
    check('group print names the individual WFH employee', str_contains($groupCell,'Synthetic Alice: WFH') && str_contains($groupCell,'Synthetic DAY') && !str_contains($groupCell,'Synthetic Bob: WFH'));
    $page = new Illuminate\Pagination\LengthAwarePaginator(collect([$group]),1,15);
    $indexHtml = view('roster.index',['data'=>$page,'super_departments'=>collect([$station]),'isDraft'=>false,'filter'=>['super_department_id'=>null,'start'=>null,'end'=>null]])->render();
    $indexCell = implode(' ',shiftCells($indexHtml));
    check('group index names the individual WFH employee', str_contains($indexCell,'Synthetic Alice: WFH') && !str_contains($indexCell,'Synthetic Bob: WFH'));
    $bothDate = '2026-11-06';
    $both = roster('Synthetic Both',[$alice->id,$bob->id],$bothDate,$shift,[$alice->id=>[$bothDate=>'WFH'],$bob->id=>[$bothDate=>'WFH']]);
    $controller->publish_draft($both);
    $both->refresh();
    $bothCell = implode(' ',shiftCells(view('roster.print',['roster'=>$both,'formatted'=>$both->formatted()])->render()));
    check('all-WFH group names both employees', str_contains($bothCell,'Synthetic Alice: WFH') && str_contains($bothCell,'Synthetic Bob: WFH'));
    $pending = roster('Synthetic Pending',[$alice->id],'2026-11-07',$shift,[$alice->id=>['2026-11-07'=>'WFH']]);
    $pendingCell = implode(' ',shiftCells(view('roster.print',['roster'=>$pending,'formatted'=>$pending->formatted()])->render()));
    check('draft remote duty is marked pending, not credited', str_contains($pendingCell,'Remote work pending attendance') && !str_contains($pendingCell,'WFH') && Attendance::where('roster_id',$pending->id)->doesntExist());
    $pending->update(['is_active'=>true]);
    $pendingCell = implode(' ',shiftCells(view('roster.print',['roster'=>$pending,'formatted'=>$pending->formatted()])->render()));
    check('active assignment without attendance is also pending', str_contains($pendingCell,'Remote work pending attendance') && !str_contains($pendingCell,'WFH'));

    // Attendance edge controls use synthetic shifts and no biometric devices.
    $night = RosterShift::create(['name'=>'Synthetic Night','code'=>'SYN-N','start'=>'22:00:00','end'=>'06:00:00','display_only'=>false]);
    $nightRoster = roster('Synthetic Night',[$bob->id],'2026-11-08',$night,[$bob->id=>['2026-11-08'=>'WFH']],true);
    $nightAtt = $service->createAttendance($nightRoster,$bob->id,'2026-11-08',$night->id);
    check('overnight WFH credits eight hours', $nightAtt->working_time==='08:00:00');
    $nightPunch = InOutRecord::create(['user_id'=>$bob->id,'status'=>'0','date_time'=>'2026-11-09 05:00:00','device_uid'=>'synthetic-offline']);
    App\Actions\EmployeePunched\Execute::run($nightPunch);
    check('overnight punch preserves previous-date WFH credit', $nightAtt->fresh()->working_time==='08:00:00' && $nightAtt->fresh()->check_out===null);
    check('unchanged WFH save is idempotent', $service->createAttendance($nightRoster,$bob->id,'2026-11-08',$night->id)->id===$nightAtt->id);
    $service->cancelAttendance($nightRoster,$bob->id,'2026-11-08');
    check('generated WFH can be cancelled', !Attendance::whereKey($nightAtt->id)->exists());
    $display = RosterShift::create(['name'=>'Synthetic Display','code'=>'SYN-X','start'=>'09:00:00','end'=>'17:00:00','display_only'=>true]);
    $invalid = roster('Synthetic Invalid',[$bob->id],'2026-11-09',$display,[],true);
    $r = $controller->update(req(payload($invalid,[$bob->id],'2026-11-09',[['id'=>$display->id]],[$bob->id=>['2026-11-09'=>'WFH']])),$invalid->id);
    check('display-only WFH rejected', $r->getStatusCode()===422);
    $r = $controller->update(req(payload($invalid,[$bob->id],'2026-11-09',[],[$bob->id=>['2026-11-09'=>'WFH']])),$invalid->id);
    check('individual WFH without a shift rejected', $r->getStatusCode()===422);
    $attendance->update(['notes'=>'Synthetic manual correction']);
    try {
        $service->cancelAttendance($source,$alice->id,$sourceDate);
        check('manually modified attendance cannot be cancelled',false);
    } catch (ValidationException $e) {
        check('manually modified attendance cannot be cancelled', $attendance->fresh()->notes==='Synthetic manual correction');
    }
    Auth::setUser($bob);
    try { $controller->publish_draft($copy); check('unauthorized publication denied',false); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { check('unauthorized publication denied', $e->getStatusCode()===403); }
} catch (Throwable $e) {
    $failed = true;
    fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\n");
} finally {
    while (DB::transactionLevel()>0) DB::rollBack();
    echo "Synthetic fixture transaction rolled back.\n";
}

exit($failed ? 1 : 0);
