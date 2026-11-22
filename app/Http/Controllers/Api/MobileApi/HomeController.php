<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    function employees()
    {
        return ApiResponseClass::sendResponse(
            User::active_employees()
                ->with([
                    'department.super_department', 'designation'
                ])->get(),
            'Employees'
        );
    }

    function app_settings(Request $request)
    {
        $info = company_data();
        $user = $request->auth_user?->toArray();
        if($user) {
            $user['live_location_track'] = $request->auth_user->live_location_track;
            $user['leave_has_attachment'] = config('services.leave_has_attachment');
            $user['leave_has_station_leave'] = config('services.leave_has_station_leave');
        }
        $approval_employees = [
            'loan' => null,
            'fine' => null,
            'leave' => null,
            'attendance_request' => null,
            'emp_daily_expense' => null,
            'inventory_item_request' => null,
            'asset_request' => null,
        ];
        if($user) {
            foreach ($approval_employees as $key => $value) {
                $approval_employees[$key] = ApprovalProcess::canApproveEmployees(
                    $key, $request->auth_user
                );
            }
        }
        return ApiResponseClass::sendResponse(
            [
                'app_version' => config('app.version'),
                'app_name' => config('app.name'),
                'app_logo' => ($info['logo'] ?? false) ? url('/uploaded_files/' . $info['logo']) : url('/assets/images/photo_defaults.jpg'),
                'ui_setting' => json_decode(
                    \App\Models\Setting::where('key', 'ui_setting')->first()?->value ?: '[]',
                    true
                ),
                'user' => $user,
                'approval_employees' => $approval_employees,
                'modules' => has_module(),
            ],
            'App Settings'
        );
    }

    function updateFirebaseToken(Request $request)
    {
        abort_if(!$request->auth_user, 401, 'Unauthorized');
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->auth_user;
        $user->firebase_token = $request->token;
        $user->save();

        return ApiResponseClass::sendResponse(
            'Token saved',
            'Token'
        );
    }
}
