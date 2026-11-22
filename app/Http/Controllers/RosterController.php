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
use App\Services\WfhAttendanceService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SuperDepartment;

class RosterController extends Controller
{

    public function __construct(protected RosterService $rosterService) {}

    function can()
    {
        if (!role_can('roster')) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        abort_if(!role_can('roster.view') && !role_can('roster'), 403);

        $isDraft = request()->is('roster/draft');

        // Filter params
        $super_department_id = request()->get('super_department_id');
        $start = request()->get('start');
        $end = request()->get('end');

        $data = $isDraft ? $this->rosterService->draft($super_department_id, $start, $end)
            : $this->rosterService->index($super_department_id, $start, $end);

        $super_departments = SuperDepartment::select('id', 'name')->ordered()->get();

        return view('roster.index', [
            'data' => $data,
            'super_departments' => $super_departments,
            'isDraft' => $isDraft,
            'filter' => compact('super_department_id', 'start', 'end'),
        ]);
    }

    public function create(Request $request)
    {
        $this->can();

        $from = $request->from ? Carbon::parse($request->from) : now();
        $to = $request->to ? Carbon::parse($request->to) : now()->addWeek();

        $employees = User::active_employees()
            ->with(['designation', 'department.super_department'])
            ->get();

        $grouped = $employees
            ->groupBy(function ($emp) {
                return $emp->department->super_department->name ?? 'Unknown Station';
            })
            ->map(function ($station) {
                return $station->groupBy(function ($emp) {
                    return $emp->department->name ?? 'Unknown Department';
                });
            });

        $super_department_id = $request->super_department_id;

        $super_departments = SuperDepartment::select('id', 'name')->ordered()->get();

        return view('roster.create', [
            'employees' => $employees,
            'employees_grouped' => $grouped,
            'super_departments' => $super_departments,
            'super_department_id' => $super_department_id,
            'dates' => generateDateRange($from, $to)
        ]);
    }

    public function store(Request $request)
    {

        $this->can();
        $formData = $request->validate([
            'name' => 'required|string|max:255',
            'super_department_id' => 'exists:super_departments,id',
            'data' => 'required|array',
            'data.*.employees' => 'required|array',
            'data.*.shifts' => 'required|array',
            ...$this->noShiftDutyRules(),
        ], $this->noShiftDutyMessages());

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

    public function edit($id)
    {
        $this->can();

        $employees = User::active_employees()
            ->with(['designation', 'department.super_department'])
            ->get();

        $grouped = $employees
            ->groupBy(function ($emp) {
                return $emp->department->super_department->name ?? 'Unknown Station';
            })
            ->map(function ($station) {
                return $station->groupBy(function ($emp) {
                    return $emp->department->name ?? 'Unknown Department';
                });
            });

        $super_departments = SuperDepartment::select('id', 'name')->ordered()->get();

        return view('roster.edit', [
            'roster' => Roster::find($id),
            'employees' => $employees,              // old array
            'employees_grouped' => $grouped,
            'super_departments' => $super_departments,
        ]);
    }



    public function update(Request $request, $id)
    {
        $this->can();
        $request->validate([
            ...$this->noShiftDutyRules(),
            'editableDates' => 'sometimes|array',
            'editableDates.*' => 'required|date_format:Y-m-d|distinct',
            'wfh_assignments' => 'sometimes|array',
            'wfh_assignments.*' => 'array',
            'wfh_assignments.*.*' => 'nullable|in:WFH',
        ], $this->noShiftDutyMessages());
// Remove empty WFH selections when Normal Duty is selected.
$wfhAssignments = [];

foreach ($request->input('wfh_assignments', []) as $employeeId => $assignments) {
    foreach ($assignments as $date => $duty) {
        if ($duty === 'WFH') {
            $wfhAssignments[$employeeId][$date] = 'WFH';
        }
    }
}

$request->merge([
    'wfh_assignments' => $wfhAssignments,
]);
        $request->validate([
    'name' => 'required|string|max:255',
    'super_department_id' => 'exists:super_departments,id',
    'data' => 'required|array',
    'data.*.employees' => 'required|array',
    'data.*.shifts' => 'required|array',

    // Individual employee WFH assignments
    'wfh_assignments' => 'sometimes|array',
    'wfh_assignments.*' => 'array',
    'wfh_assignments.*.*' => 'nullable|in:WFH',
]);

        $roster = Roster::findOrFail($id);

// Preserve previous WFH assignments for safe cancellation.
$oldWfhAssignments = $roster->wfh_assignments ?? [];

// Validate individual WFH employee IDs and dates.
$rosterEmployeeIds = collect($request->input('data', []))
    ->flatMap(fn ($row) => $row['employees'] ?? [])
    ->map(fn ($employeeId) => (string) $employeeId)
    ->unique()
    ->all();

$validDates = collect($request->input('data', []))
    ->flatMap(fn ($row) => array_keys($row['shifts'] ?? []))
    ->unique()
    ->all();

foreach ($request->input('wfh_assignments', []) as $employeeId => $assignments) {
    if (!in_array((string) $employeeId, $rosterEmployeeIds, true)) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'wfh_assignments' => 'WFH employee is not included in this roster.',
        ]);
    }

    foreach ($assignments as $date => $duty) {
        if (!in_array($date, $validDates, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'wfh_assignments' => 'WFH date is outside the roster dates.',
            ]);
        }
    }
}

DB::beginTransaction();

        try {

            // --- COPY editableDates TO LOCAL VARIABLE ---
            $editableDates = $request->input('editableDates', []);

            $oldStart = Carbon::parse($roster->start)->format('Y-m-d');
            $oldEnd   = Carbon::parse($roster->end)->format('Y-m-d');

            $newStart = count($editableDates) ? $editableDates[0] : $oldStart;
            $newEnd   = count($editableDates) ? end($editableDates) : $oldEnd;

            $dateChanged = ($oldStart !== $newStart) || ($oldEnd !== $newEnd);

            // UPDATE ROSTER MAIN
$roster->update([
    'name' => $request->name,
    'super_department_id' => $request->super_department_id,
    'created_by' => user()->id,
    'start' => $newStart,
    'end' => $newEnd,
    'no_shift_duty' => $this->normalizeNoShiftDuty($request->no_shift_duty, $request->data),
    'wfh_assignments' => $request->input('wfh_assignments', []),
]);

            // Rebuild roster items without deleting existing attendance.
            $roster->items()->delete();

            // CREATE NEW ITEMS
            $allDates = $editableDates ?: array_keys($request->data[0]['shifts']);

            $bulkInsert = [];

            foreach ($request->data as $rowIndex => $item) {

                $group = $item['group'] ?? $rowIndex;
                $order = $item['order'] ?? $rowIndex;

                foreach ($allDates as $date) {

                    $shifts = $item['shifts'][$date] ?? [];

                    if (empty($shifts)) {
                        $shifts = [null];
                    }

                    foreach (($shifts ?: [null]) as $shift) {

                        foreach ($item['employees'] as $employee) {

                            $bulkInsert[] = [
                                'roster_id' => $roster->id,
                                'employee_id' => $employee,
                                'roster_shift_id' => $shift['id'] ?? null,
                                'date' => $date,

                                // ✅ FIXED SEPARATION
                                'group' => $group,
                                'order' => $order,

                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }

                        // Validate WFH against actual employee/date roster shifts.
            foreach ($request->input('wfh_assignments', []) as $employeeId => $assignments) {
                foreach ($assignments as $date => $duty) {
                    if ($duty !== 'WFH') {
                        continue;
                    }

                    $matchingItems = collect($bulkInsert)->filter(
                        fn ($item) =>
                            (string) $item['employee_id'] === (string) $employeeId
                            && $item['date'] === $date
                            && !empty($item['roster_shift_id'])
                    );

                    if ($matchingItems->isEmpty()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'wfh_assignments' => "Employee {$employeeId} has no shift on {$date}.",
                        ]);
                    }

                    $shiftIds = $matchingItems->pluck('roster_shift_id')->unique();

                    if ($shiftIds->count() !== 1 ||
                        !RosterShift::whereKey($shiftIds->first())
                            ->where('display_only', false)
                            ->exists()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'wfh_assignments' => "Employee {$employeeId} has an invalid or ambiguous shift on {$date}.",
                        ]);
                    }
                }
            }

            RosterItem::insert($bulkInsert);
// Synchronize WFH attendance only for active rosters.
if ($roster->is_active) {
    $wfhService = app(WfhAttendanceService::class);
    $newWfhAssignments = $request->input('wfh_assignments', []);

    // Cancel removed WFH assignments.
    foreach ($oldWfhAssignments as $employeeId => $dates) {
        foreach ($dates as $date => $duty) {
            if ($duty !== 'WFH') {
                continue;
            }

            if (($newWfhAssignments[$employeeId][$date] ?? null) !== 'WFH') {
                $wfhService->cancelAttendance(
                    $roster,
                    (int) $employeeId,
                    $date
                );
            }
        }
    }

    // Create new WFH attendance and synchronize changed shifts.
    foreach ($newWfhAssignments as $employeeId => $dates) {
        foreach ($dates as $date => $duty) {
            if ($duty !== 'WFH') {
                continue;
            }

            $shiftIds = collect($bulkInsert)
                ->filter(fn ($item) =>
                    (string) $item['employee_id'] === (string) $employeeId
                    && $item['date'] === $date
                    && !empty($item['roster_shift_id'])
                )
                ->pluck('roster_shift_id')
                ->unique();

            $newShiftId = (int) $shiftIds->first();

            $existingWfh = Attendance::query()
                ->where('roster_id', $roster->id)
                ->where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->where('duty_type', 'Work From Home')
                ->first();

            if (
                $existingWfh
                && (int) $existingWfh->roster_shift_id !== $newShiftId
            ) {
                $wfhService->cancelAttendance(
                    $roster,
                    (int) $employeeId,
                    $date
                );
            }

            $wfhService->createAttendance(
                $roster,
                (int) $employeeId,
                $date,
                $newShiftId
            );
        }
    }
}
            // ATTENDANCE RECALCULATION
            Attendance::query()
                ->where('roster_id', $id)
                ->with('used_in_out_records')
                ->chunk(50, function ($attendances) {
                    foreach ($attendances as $attendance) {
                        if ($attendance->used_in_out_records->first()) {
                            EmployeePunchedJob::dispatch(
    $attendance->used_in_out_records->first(),
    $attendance,
)->afterCommit();
                        }
                    }
                });

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Roster updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
    DB::rollBack();

    return response()->json([
        'success' => false,
        'message' => 'Invalid WFH or roster assignment.',
        'errors' => $e->errors(),
    ], 422);

} catch (\Exception $e) {
    DB::rollBack();

    Log::error('Roster update failed', [
        'roster_id' => $id,
        'exception' => $e,
    ]);

    return response()->json([
        'success' => false,
        'message' => 'Failed to update roster. Please try again.',
    ], 500);
}
    }

    private function noShiftDutyRules(): array
    {
        return [
            'no_shift_duty' => 'nullable|array',
            'no_shift_duty.*' => 'array',
            'no_shift_duty.*.*' => 'nullable|string|not_in:WFH',
        ];
    }

    private function noShiftDutyMessages(): array
    {
        return [
            'no_shift_duty.*.*.not_in' =>
                'Select an original shift and assign WFH to individual employees in the roster editor.',
        ];
    }

    private function normalizeNoShiftDuty($noShiftDuty, $data)
    {
        $result = [];

        foreach ($data as $index => $row) {

            $uid = $row['uid'] ?? null;

            if (!$uid) {
                continue;
            }

            $result[$index] = $noShiftDuty[$uid] ?? [];
        }

        return $result;
    }

    public function delete($id)
    {
        $this->can();
        $roster = Roster::find($id);
        $roster->items()->delete();
        $roster->delete();
        Toastr::success('Roster deleted successfully');
        return redirect()->back()->with('success', 'Roster deleted successfully');
    }

    public function shifts()
    {
	    /* dd([
            'auth_check' => auth()->check(),
            'session_id' => session()->getId(),
            'cookie' => request()->cookie(),
            'user' => auth()->user(),
        ]);
	     */
        $this->can();
        return RosterShift::all();
    }

    public function shifts_store(Request $request)
    {
        $this->can();
        return RosterShift::create($request->all());
    }

    public function shifts_update(Request $request, RosterShift $shift)
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

    public function print($id)
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
    $this->can();

    try {
        DB::transaction(function () use ($roster) {
            $roster = Roster::query()
                ->whereKey($roster->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($roster->is_active) {
                return;
            }

            validator(['no_shift_duty' => $roster->no_shift_duty], $this->noShiftDutyRules(),
                $this->noShiftDutyMessages())->validate();

            $wfhAssignments = $roster->wfh_assignments ?? [];
            $wfhService = app(WfhAttendanceService::class);

            $roster->update([
                'is_active' => true,
            ]);

            foreach ($wfhAssignments as $employeeId => $dates) {
                foreach ($dates as $date => $duty) {
                    if ($duty !== 'WFH') {
                        continue;
                    }

                    $shiftIds = $roster->items()
                        ->where('employee_id', $employeeId)
                        ->whereDate('date', $date)
                        ->whereNotNull('roster_shift_id')
                        ->distinct()
                        ->pluck('roster_shift_id');

                    if ($shiftIds->count() !== 1) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'wfh_assignments' =>
                                "Employee {$employeeId} on {$date} must have exactly one original shift.",
                        ]);
                    }

                    $wfhService->createAttendance(
                        $roster,
                        (int) $employeeId,
                        $date,
                        (int) $shiftIds->first()
                    );
                }
            }
        }, 3);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()->withErrors($e->errors());
    } catch (\Throwable $e) {
        Log::error('Roster publication failed', [
            'roster_id' => $roster->id,
            'error' => $e->getMessage(),
        ]);

        return redirect()->back()->withErrors([
            'roster' => 'Roster publication failed. No changes were saved.',
        ]);
    }

    Toastr::success('Roster published successfully');

    return redirect()->back()
        ->with('success', 'Roster published successfully');
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
        // Copies clear original shifts below, so WFH must be selected again too.
        $copy->wfh_assignments = [];

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
                        // $newRow[$key] = $value;
                        $newRow[$key] = "";
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

                        $newRow[$newDate] = "";
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
            $item_copy->roster_shift_id = null;
            $item_copy->date = $newDate;
            $item_copy->save();
        }

        Toastr::success('Roster copied successfully');

        return redirect()
            ->route('roster.edit', $copy->id)
            ->with('copy_mode', true);
    }

    public function printFiltered()
    {
        $this->can();

        $super_department_id = request('super_department_id');
        $start = request('start');
        $end = request('end');
        $status = request('status');

        $rosters = Roster::with('items')
            ->when($status !== null, function ($q) use ($status) {
                $q->where('is_active', $status);
            })
            ->when($super_department_id, function ($q) use ($super_department_id) {
                $q->where('super_department_id', $super_department_id);
            })
            ->when($start, function ($q) use ($start) {
                $q->whereDate('start', '>=', $start);
            })
            ->when($end, function ($q) use ($end) {
                $q->whereDate('end', '<=', $end);
            })
            ->orderBy('start')
            ->get();

        return view('roster.print_multiple', [
            'rosters' => $rosters
        ]);
    }

}
