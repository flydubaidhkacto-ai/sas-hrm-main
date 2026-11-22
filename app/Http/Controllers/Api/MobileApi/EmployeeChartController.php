<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LeaveApplication;
use App\Classes\ApiResponseClass;
use Illuminate\Http\Request;

class EmployeeChartController extends Controller
{
    public function chartOfEmployees()
    {
        $today = today();

        $today_present = User::active_employees()
            ->whereHas('attendance', function ($query) use ($today) {
                $query->where('date', $today);
            })->count();

        $today_absent = User::absent($today)->count();

        $today_leave = LeaveApplication::final_approved()->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();

        $active_employees = User::active_employees()->count();

        $data = [
            'today_present' => $today_present,
            'today_absent' => $today_absent,
            'today_leave' => $today_leave,
            'active_employees' => $active_employees,
        ];
        return ApiResponseClass::sendResponse($data, 'Employee Chart Data');
    }
}
