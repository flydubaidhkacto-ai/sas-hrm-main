<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Classes\ApiResponseClass;
use App\Traits\GenerateTotalDutyOfEmployee;

class ReportController extends Controller
{
    use GenerateTotalDutyOfEmployee;
    public function summary_report(Request $request)
    {
        // Log::info($request->all());
        $user = $request->auth_user;

        abort_if(!(
            role_can('reports', $user)
            || role_can('reports.self.super_department', $user)
            || role_can('reports.self', $user)
        ), 403);

        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        // limit from-to days to 31 days
        if ($to->diffInDays($from) > 31) {
            throw new \Exception('Date range must be less than 31 days');
        }

        $data = Attendance::with('employee.designation')
            ->with(['employee.designation', 'employee.department.super_department', 'shift']);
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            $data->whereDate('date', $from->format('Y-m-d'));
        } else {
            $data->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }

        if (
            !role_can('reports', $user)
            && !role_can('reports.self.super_department', $user)
            && role_can('reports.self', $user)
        ) {
            $data->where('employee_id', $user->id);
        }

        if ($request->employee_id) {
            $data->where('employee_id', $request->employee_id);
        }

        // Log::info($data->get());

        return ApiResponseClass::sendResponse(
            $data->get()->map(function ($item) {
                $item->in_out_data = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                    $item,
                    true
                );
                return $item;
            }),
            'Attendance Retrive Successfully'
        );
    }
    public function attendance(Request $request)
    {
        // Log::info($request->all());
        $user = $request->auth_user;

        abort_if(!(
            role_can('reports', $user)
            || role_can('reports.self.super_department', $user)
            || role_can('reports.self', $user)
        ), 403);

        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        // limit from-to days to 31 days
        if ($to->diffInDays($from) > 31) {
            throw new \Exception('Date range must be less than 31 days');
        }

        $data = Attendance::with('employee.designation')
            ->with(['employee.designation', 'employee.department.super_department', 'shift']);
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            $data->whereDate('date', $from->format('Y-m-d'));
        } else {
            $data->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }

        if (
            !role_can('reports', $user)
            && !role_can('reports.self.super_department', $user)
            && role_can('reports.self', $user)
        ) {
            $data->where('employee_id', $user->id);
        }

        if ($request->employee_id) {
            $data->where('employee_id', $request->employee_id);
        }

        // Log::info($data->get());

        return ApiResponseClass::sendResponse(
            $data->get(),
            'Attendance Retrive Successfully'
        );
    }

    public function late(Request $request)
    {
        $user = $request->auth_user;

        $from = $request->start_date ? carbon($request->start_date)->startOfDay() : carbon()->subDay()->startOfDay();
        $to = $request->end_date ? carbon($request->end_date)->endOfDay() : now()->endOfDay();

        $att = Attendance::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to);

        abort_if(!(
            role_can('reports', $user)
            || role_can('reports.self.super_department', $user)
            || role_can('reports.self', $user)
        ), 403);
        if (
            !role_can('reports', $user)
            && !role_can('reports.self.super_department', $user)
            && role_can('reports.self', $user)
        ) {
            $att->where('employee_id', $user->id);
        }

        if ($request->employee_id) {
            $att = $att->where('employee_id', $request->employee_id);
        }


        $att = $att->with(['employee.designation', 'employee.department.super_department', 'shift'])
            ->where('got_late_check_in', true)
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                $item->late_time_missed = late_time_missed($item->shift, $item->worked_from);
                return $item;
            });

        return ApiResponseClass::sendResponse(
            $att,
            'Late Attendance Report'
        );
    }

    public function absent(Request $request)
    {
        $user = $request->auth_user;

        $from = $request->start_date ? carbon($request->start_date)->startOfDay() : carbon()->startOfDay();
        $to = $request->end_date ? carbon($request->end_date)->endOfDay() : now()->endOfDay();

        abort_if(!(
            role_can('reports', $user)
            || role_can('reports.self.super_department', $user)
            || role_can('reports.self', $user)
        ), 403);
        $employees = [];
        $currentDate = $from->copy();
        while ($currentDate->lte($to)) {
            $tmp_users = User::absent($currentDate);

            if (
                !role_can('reports', $user)
                && !role_can('reports.self.super_department', $user)
                && role_can('reports.self', $user)
            ) {
                $tmp_users->where('id', $user->id);
            }

            if ($request->employee_id) {
                $tmp_users->where('id', $request->employee_id);
            }

            $tmp_users->with(['department.super_department', 'designation'])
                ->orderBy('name', 'asc');

            $tmp_users = $tmp_users
                ->get();
            $employees[$currentDate->format('Y_m_d')] = $tmp_users;
            $currentDate->addDay(1);
        }

        // dd($employees);
        return ApiResponseClass::sendResponse(
            $employees,
            'Absent Attendance Report'
        );
    }

    public function emp_attendance(Request $request)
    {
        $user = $request->auth_user;

        $request->validate([
            'employee' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        abort_if(!(
            role_can('reports', $user)
            || role_can('reports.self.super_department', $user)
            || role_can('reports.self', $user)
        ), 403);

        if (
            !role_can('reports', $user)
            && !role_can('reports.self.super_department', $user)
            && role_can('reports.self', $user)
        ) {
            $employee = $user;
        } else {
            $employee = User::find($request->employee_id);
        }
        $data = [];
        if ($employee) {
            $data = $this->generate_total_duty_of_employee($employee, $from, $to);
        }

        return ApiResponseClass::sendResponse(
            $data,
            'Employee Attendance Report'
        );
    }
}
