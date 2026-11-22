<?php

namespace App\Http\Controllers;

use App\Models\EmployeeWeeklySchedule;
use App\Models\GeoLocationCheckIn;
use App\Models\Location;
use App\Models\Notification;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeWeeklyScheduleController extends Controller
{
    /**
     * Display a listing of employees and their weekly schedules.
     */
    public function index(Request $request)
    {
        abort_if(!role_can('locations') && !user()->geo_location_departments, 403, "Need Permission!");

        $employees = User::active_employees()
            ->with(['weekly_schedules.location', 'department', 'designation'])
            ->where('have_geo_location', true)
            ->when(!role_can('locations') && user()->geo_location_departments, function ($q) {
                $q->whereHas('department', fn($q2) => $q2->whereIn('super_department_id', user()->geo_location_departments));
            })
            ->get();
        $date = $request->date ? Carbon::parse($request->date) : today();
        return view('employees.weekly-schedules.index', [
            'employees' => $employees,
            'attendances' => GeoLocationCheckIn::whereDate('check_at', $date)->get(),
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * Show the form for editing the specified employee's weekly schedule.
     */
    public function edit(User $employee)
    {
        abort_if(!$employee->have_geo_location, 403);
        $employee->load(['assigned_locations', 'weekly_schedules']);
        $weekdays = EmployeeWeeklySchedule::getWeekdays();

        // Group existing schedules by weekday for easier display
        $existingSchedules = $employee->weekly_schedules->groupBy('weekday');

        return view('employees.weekly-schedules.edit', [
            'employee' => $employee,
            'weekdays' => $weekdays,
            'existingSchedules' => $existingSchedules,
        ]);
    }

    /**
     * Update the specified employee's weekly schedule.
     */
    public function update(Request $request, User $employee)
    {
        abort_if(!role_can('locations') && !user()->geo_location_departments, 403, "Need Permission!");
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.weekday' => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'schedules.*.location_ids' => 'nullable|array',
            'schedules.*.location_ids.*' => 'exists:locations,id',
            'schedules.*.notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $employee) {
            // Delete existing schedules for this employee
            $employee->weekly_schedules()->delete();

            // Create new schedules
            foreach ($request->schedules as $schedule) {
                if (isset($schedule['location_ids']) && !empty($schedule['location_ids'])) {
                    foreach ($schedule['location_ids'] as $locationId) {
                        // Verify the location is assigned to the employee
                        if ($employee->assigned_locations->contains('id', $locationId)) {
                            EmployeeWeeklySchedule::create([
                                'user_id' => $employee->id,
                                'location_id' => $locationId,
                                'weekday' => $schedule['weekday'],
                                'notes' => $schedule['notes'] ?? null,
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }

            // Send notification to employee
            $this->sendScheduleUpdateNotification($employee);
        });

        Toastr::success('Weekly schedule updated successfully!');

        return redirect()->route('employees.weekly-schedules.index');
    }

    /**
     * Display the employee's own weekly schedule.
     */
    public function show(Request $request)
    {
        $employee = $request->user();

        if ($employee->role !== 'employee') {
            abort(403, 'Only employees can view their schedules.');
        }

        $weekdays = EmployeeWeeklySchedule::getWeekdays();
        $schedules = $employee->weekly_schedules()
            ->with('location')
            ->active()
            ->get()
            ->groupBy('weekday');

        // Get today's schedules
        $todayWeekday = strtolower(now()->format('l'));
        $todaySchedules = $schedules->get($todayWeekday, collect());

        return view('employees.weekly-schedules.show', [
            'employee' => $employee,
            'weekdays' => $weekdays,
            'schedules' => $schedules,
            'todaySchedules' => $todaySchedules,
            'todayWeekday' => $todayWeekday,
        ]);
    }

    /**
     * Get eligible locations for today for the authenticated user.
     */
    public function getTodayEligibleLocations(Request $request)
    {
        $employee = $request->user();
        $todayWeekday = strtolower(now()->format('l'));

        $eligibleLocationIds = EmployeeWeeklySchedule::forUser($employee->id)
            ->forWeekday($todayWeekday)
            ->active()
            ->pluck('location_id')
            ->toArray();

        $eligibleLocations = $employee->assigned_locations()
            ->whereIn('locations.id', $eligibleLocationIds)
            ->get();

        return response()->json([
            'eligible_locations' => $eligibleLocations,
            'weekday' => ucfirst($todayWeekday),
        ]);
    }

    /**
     * Send notification to employee about schedule update.
     */
    private function sendScheduleUpdateNotification(User $employee)
    {
        Notification::create([
            'title' => 'Weekly Schedule Updated',
            'message' => 'Your weekly work schedule has been updated by HR admin. Please check your schedule.',
            'user_id' => $employee->id,
            'mobile_url' => '/employee/weekly-schedule',
            'url' => route('employees.weekly-schedules.show'),
            'time_of_notify' => now(),
            // 'type' => 'schedule_update',
        ]);
    }

    /**
     * Remove a specific schedule.
     */
    public function destroy(EmployeeWeeklySchedule $schedule)
    {
        abort_if(!role_can('locations') && !user()->geo_location_departments, 403, "Need Permission!");
        $employeeName = $schedule->employee->name;
        $schedule->delete();

        Toastr::success("Schedule removed for {$employeeName}");

        return back();
    }

    /**
     * Toggle schedule status.
     */
    public function toggleStatus(EmployeeWeeklySchedule $schedule)
    {
        abort_if(
            !role_can('locations')
            && !user()->geo_location_departments,
            403, "Need Permission!"
        );

        $schedule->update(['is_active' => !$schedule->is_active]);

        $status = $schedule->is_active ? 'activated' : 'deactivated';
        Toastr::success("Schedule {$status} successfully!");

        return back();
    }

    /**
     * Get employees with their weekly schedules (API endpoint).
     */
    public function getEmployeesSchedules(Request $request)
    {
        $employees = User::active_employees()
            ->where('have_geo_location', true)
            ->with(['weekly_schedules.location'])
            ->when($request->department_id, function ($query) use ($request) {
                return $query->where('department_id', $request->department_id);
            })
            ->get();

        return response()->json($employees);
    }
}
