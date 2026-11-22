<?php

namespace App\Http\Controllers;

use App\Interfaces\RosterRepositoryInterface;
use App\Jobs\EmployeePunchedJob;
use App\Models\Attendance;
use App\Models\Roster;
use App\Models\RosterItem;
use App\Models\RosterShift;
use App\Models\User;
use App\Services\RosterService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RosterController extends Controller
{

    public function __construct(protected RosterService $rosterService) {}

function can()
{
    if (!role_can('roster')) {
        abort(403, 'Unauthorized');
    }
}

public
function index()
{
    abort_if(!role_can('roster.view') && !role_can('roster'), 403);

    $isDraft = request()->is('roster/draft');
    $data = $isDraft ? $this->rosterService->draft() : $this->rosterService->index();

    return view('roster.index', [
        'data' => $data,
        'isDraft' => $isDraft,
    ]);
}

public
function create(Request $request)
{
    $this->can();
    $from = $request->from ? Carbon::parse($request->from) : now();
    $to = $request->to ? Carbon::parse($request->to) : now()->addWeek();
    return view('roster.create', [
        'employees' => User::active_employees()
            ->get(),
        'dates' => generateDateRange($from, $to),
    ]);
}

public
function store(Request $request)
{
    $this->can();
    $formData = $request->validate([
        'name' => 'required|string|max:255',
        'data' => 'required|array',
        'data.*.employees' => 'required|array',
        'data.*.shifts' => 'required|array',
        'no_shift_duty' => 'nullable'
    ]);
    // dd($request->all());
    DB::beginTransaction();
    try {
        $roster = $this->rosterService->store($formData);
        DB::commit();
        return $roster;
    } catch (\Exception $e) {
        Log::error($e);
        DB::rollBack();
        Toastr::error($e->getMessage() ?: 'Failed to create roster. Please try again.');
    }

    return null;
}

public
function edit($id)
{
    $this->can();
    return view('roster.edit', [
        'roster' => Roster::find($id),
        'employees' => User::active_employees()->get(),
    ]);
}

public
function update(Request $request, $id)
{
    $this->can();

    $request->validate([
        'name' => 'required|string|max:255',
        'data' => 'required|array',
        'data.*.employees' => 'required|array',
        'data.*.shifts' => 'required|array',
    ]);

    $roster = Roster::findOrFail($id);

    /* ------------------------------------
       DATE MAPPING (ONLY FOR COPY MODE)
    -------------------------------------*/

    $editableDates = $request->editableDates ?? null;
    $dateMap = [];

    if ($editableDates) {
        // old dates from shifts
        $oldDates = array_keys($request->data[0]['shifts']);

        foreach ($oldDates as $index => $oldDate) {
            if (isset($editableDates[$index])) {
                $dateMap[$oldDate] = $editableDates[$index];
            }
        }
    }

    /* ------------------------------------
       UPDATE ROSTER MAIN
    -------------------------------------*/

    $startDate = $editableDates ? $editableDates[0] : array_keys($request->data[0]['shifts'])[0];
    $endDate = $editableDates ? end($editableDates) : array_keys($request->data[0]['shifts'])[count($request->data[0]['shifts']) - 1];

    $roster->update([
        'name' => $request->name,
        'created_by' => user()->id,
        'start' => $startDate,
        'end' => $endDate,
        'no_shift_duty' => $request->no_shift_duty,
    ]);

    /* ------------------------------------
       DELETE OLD ITEMS
    -------------------------------------*/
    $roster->items()->delete();

    /* ------------------------------------
       CREATE NEW ITEMS WITH UPDATED DATES
    -------------------------------------*/
    foreach ($request->data as $i => $item) {
        foreach ($item['shifts'] as $date => $shifts) {

            // ⭐ replace old date with new date
            $finalDate = $dateMap[$date] ?? $date;

            foreach (($shifts ?? null) ?: [null] as $shift) {
                foreach ($item['employees'] as $employee) {
                    $roster->items()->create([
                        'employee_id' => $employee,
                        'roster_shift_id' => $shift ? $shift['id'] : null,
                        'date' => $finalDate,
                        'group' => $i,
                    ]);
                }
            }
        }
    }

    /* ------------------------------------
       ATTENDANCE RECALCULATION
    -------------------------------------*/
    Attendance::query()
        ->where('roster_id', $id)
        ->with('used_in_out_records')
        ->chunk(50, function ($attendances) use ($roster) {
            foreach ($attendances as $attendance) {
                if ($attendance->used_in_out_records->first()) {
                    EmployeePunchedJob::dispatch(
                        $attendance->used_in_out_records->first(),
                        $attendance,
                        );
                }
            }
        });

    return $roster;
}

public
function delete($id)
{
    $this->can();
    $roster = Roster::find($id);
    $roster->items()->delete();
    $roster->delete();
    Toastr::success('Roster deleted successfully');
    return redirect()->back()->with('success', 'Roster deleted successfully');
}

public
function shifts()
{
    $this->can();
    return RosterShift::all();
}

public
function shifts_store(Request $request)
{
    $this->can();
    return RosterShift::create($request->all());
}

public
function shifts_update(Request $request, RosterShift $shift)
{
    $this->can();
    $shift->update($request->all());
    return $shift;
}

function shifts_delete($shift)
{
    $this->can();
    RosterShift::query()->where('id', $shift)->delete();
    return [];
}

public
function print($id)
{
$this->can(); // Ensure permissions are checked

        $roster = Roster::with('items')->findOrFail($id); // Load roster with related items
        $formatted = $roster->formatted(); // Format the roster data
        // dd([$roster, $formatted]);
        return view('roster.print', [
            'roster' => $roster,
            'formatted' => $formatted,
        ]);
    }
    public function publish_draft(Roster $roster)
{
    $roster->update([
        'is_active' => true,
    ]);
    // dd($roster);
    Toastr::success('Roster published successfully');
    return redirect()->back()->with('success', 'Roster published successfully');
}
    public function my(Request $request)
{

    if (role_can('roster')) {
        $user = $request->employee ?: user()->id;
    } else {
        $user = user()->id;
    }
    $from = ($request->start ? Carbon::parse($request->start) : now()->subDays(7));
    $to = ($request->end ? Carbon::parse($request->end) : now()->addDays(14));
    $dates = generateDateRange($from, $to);

    $items = [];
    $roster = null;
    foreach ($dates as $date) {
        $item = RosterItem::where('employee_id', $user)
            ->whereHas('roster', function ($query) {
                $query->where('is_active', true);
            })
            ->where('date', $date)
            ->with(['shift', 'roster'])
            ->first();

        if ($item && $item->roster) {
            $roster = $item->roster;
        }
        $data = $item ?: [
            'date' => $date,
            'shift' => null,
            'not_set' => true,
        ];

        if ($roster && $item) {
            $data['no_shift_duty'] = $roster->no_shift_duty[$item->group][$date] ?? null;
        }
        $items[] = $data;
    }
    return view('roster.my', [
        'items' => $items,
        'roster' => $roster,
        'from' => $from,
        'to' => $to,
        'user' => User::find($user),
    ]);
}

    public function copy($id)
{
    $this->can();

    $roster = Roster::findOrFail($id);

    $start = Carbon::parse($roster->start);
    $end = Carbon::parse($roster->end);

    $days = $start->diffInDays($end) + 1;

    $newStart = $end->copy()->addDay();
    $newEnd = $newStart->copy()->addDays($days - 1);

    /*
    -------------------------
    CREATE NEW ROSTER
    -------------------------
    */

    $copy = $roster->replicate();
    $copy->name = $roster->name . ' Copy';
    $copy->start = $newStart;
    $copy->end = $newEnd;

    /*
    -------------------------
    FIX no_shift_duty
    -------------------------
    */

    $oldNoDuty = $roster->no_shift_duty; // already array
    $newNoDuty = [];

    if ($oldNoDuty) {

        foreach ($oldNoDuty as $row) {

            $newRow = [];

            foreach ($row as $key => $value) {

                // group index
                if (is_numeric($key)) {
                    $newRow[$key] = $value;
                    continue;
                }

                // date key
                if (strtotime($key)) {

                    $oldDate = Carbon::parse($key);

                    $offset = $start->diffInDays($oldDate);

                    $newDate = $newStart
                        ->copy()
                        ->addDays($offset)
                        ->format('Y-m-d');

                    $newRow[$newDate] = $value;
                }
            }

            $newNoDuty[] = $newRow;
        }
    }

    $copy->no_shift_duty = $newNoDuty;

    $copy->is_active = 0;

    $copy->save();

    /*
    -------------------------
    COPY roster_items
    -------------------------
    */

    $items = $roster->items()->get();

    foreach ($items as $item) {

        $oldDate = Carbon::parse($item->date);

        $offset = $start->diffInDays($oldDate);

        $newDate = $newStart->copy()->addDays($offset);

        $item_copy = $item->replicate();
        $item_copy->roster_id = $copy->id;
        $item_copy->date = $newDate;
        $item_copy->save();
    }

    Toastr::success('Roster copied successfully');

    return redirect()
        ->route('roster.edit', $copy->id)
        ->with('copy_mode', true);
}
}
