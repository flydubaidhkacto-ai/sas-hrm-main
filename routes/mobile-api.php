<?php

use App\Http\Controllers\Api\MobileApi\AuthController;
use App\Http\Controllers\Api\MobileApi\AttendanceController;
use App\Http\Controllers\Api\MobileApi\HomeController;
use App\Http\Controllers\Api\MobileApi\InventoryItemController;
use App\Http\Controllers\Api\MobileApi\LiveLocationController;
use App\Http\Controllers\Api\MobileApi\LeaveApplicationController;
use App\Http\Controllers\Api\MobileApi\LoanController;
use App\Http\Controllers\Api\MobileApi\NoticeController;
use App\Http\Controllers\Api\MobileApi\EmpDailyExpenseCategoryController;
use App\Http\Controllers\Api\MobileApi\EmpDailyExpenseController;
use App\Http\Controllers\Api\MobileApi\RosterController;
use App\Http\Controllers\Api\MobileApi\RewardController;
use App\Http\Controllers\Api\MobileApi\FineController;
use App\Http\Controllers\Api\MobileApi\EmployeeChartController;
use App\Http\Controllers\Api\MobileApi\ReportController;
use App\Http\Controllers\Api\MobileApi\NotificationController;
use App\Http\Controllers\Api\MobileApi\ApprovalController;
use App\Http\Controllers\Api\MobileApi\AttendanceRequestController;
use App\Http\Controllers\Api\MobileApi\InventoryItemRequestController;
use App\Http\Controllers\DataQueryRequestController;
use App\Http\Controllers\LeaveApplicationController as ControllersLeaveApplicationController;
use App\Http\Controllers\MobileApi\PayrollController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/qr-auth', [\App\Http\Controllers\MobileAppController::class, 'authenticateQR']);
Route::get('user', [AuthController::class, 'user']);

    // Route::post('live-location/store', [LiveLocationController::class, 'store']);
Route::middleware('mobile-api')->group(function () {

    Route::post('change-password', [AuthController::class, 'changePassword']);

    // Device Management Routes
    Route::get('device-sessions', [AuthController::class, 'getDeviceSessions']);
    Route::post('device-sessions/terminate', [AuthController::class, 'terminateDeviceSession']);
    Route::post('device-sessions/terminate-others', [AuthController::class, 'terminateOtherDeviceSessions']);

    Route::get('calendar', [AttendanceController::class, 'calendar']);
    Route::get('live-location', [LiveLocationController::class, 'get']);
    Route::post('live-location/store', [LiveLocationController::class, 'store']);
    Route::post('outside-duty/punch', [AttendanceController::class, 'outSidePunch']);
    // Route::post('outside-duty/entry', [AttendanceController::class, 'outSideEntry']);
    // Route::post('outside-duty/out', [AttendanceController::class, 'outSideOut']);
    Route::get('inouts/list', [AttendanceController::class, 'in_outs']);
    Route::get('attendance/status', [AttendanceController::class, 'attendance_status']);
    Route::get('attendance-records', [AttendanceController::class, 'inOutRecords']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('leaves', [LeaveApplicationController::class, 'index']);
    Route::get('leaves/show/{id}', [LeaveApplicationController::class, 'show']);
    Route::get('/leaves/types', [LeaveApplicationController::class, 'types']);
    Route::get('/leaves/balance', [LeaveApplicationController::class, 'balance']);
    Route::post('leaves', [LeaveApplicationController::class, 'store']);
    Route::get('/leaves/{id}', [LeaveApplicationController::class, 'show']);

    Route::get('loans', [LoanController::class, 'index']);
    Route::get('loans/show/{loan}', [LoanController::class, 'show']);
    Route::post('loans/apply', [LoanController::class, 'apply']);

    Route::get('notices', [NoticeController::class, 'notices']);

    Route::get('/employees', [HomeController::class, 'employees']);


    // Employee Expenses
    Route::get('emp-expense/categories', [EmpDailyExpenseCategoryController::class, 'index']);
    Route::post('/emp-expense/categories', [EmpDailyExpenseCategoryController::class, 'store']);
    Route::delete('/emp-expense/categories/{id}/delete', [EmpDailyExpenseCategoryController::class, 'delete']);
    Route::get('/emp-expense/categories/{id}/edit', [EmpDailyExpenseCategoryController::class, 'edit']);
    Route::put('/emp-expense/categories/{id}', [EmpDailyExpenseCategoryController::class, 'update']);


    Route::prefix('roster')->group(function () {
        Route::get('/', [RosterController::class, 'index']);
        Route::get('/{id}', [RosterController::class, 'show']);
        // Route::post('/', [RosterController::class, 'shifts_store']);
        // Route::put('/{shift}', [RosterController::class, 'shifts_update']);
        // Route::delete('/{shift}', [RosterController::class, 'shifts_delete']);
    });

    // Inventory
    Route::get('/inventory/item/request', [InventoryItemController::class, 'index']);
    Route::post('/inventory/item/store', [InventoryItemController::class, 'store']);
    Route::get('/inventory/item/show/{item}', [InventoryItemController::class, 'show']);
    Route::get('/inventory/select', [InventoryItemController::class, 'selectInventory']);
    Route::get('/inventory-item-requests', [InventoryItemRequestController::class, 'index']);
    Route::get('/inventory-item-requests/create', [InventoryItemRequestController::class, 'create']);
    Route::post('/inventory-item-requests', [InventoryItemRequestController::class, 'store']);
    Route::get('/inventory-item-requests/{id}', [InventoryItemRequestController::class, 'show']);

    // rewards
    Route::get('reward', [RewardController::class, 'index']);
    Route::post('/reward', [RewardController::class, 'store']);
    Route::delete('reward/{id}', [RewardController::class, 'destroy']);

    // fines
    Route::get('fine', [FineController::class, 'index']);
    Route::post('/fine', [FineController::class, 'store']);
    Route::get('/fine/{id}', [FineController::class, 'show']);


    Route::get('emp-expense', [EmpDailyExpenseController::class, 'index']);
    Route::get('emp-expense/create', [EmpDailyExpenseController::class, 'create']);
    Route::post('/emp-expense', [EmpDailyExpenseController::class, 'store']);
    Route::get('/emp-expense/{id}', [EmpDailyExpenseController::class, 'show']);
    Route::get('/get-all-employees', [EmpDailyExpenseController::class, 'allEmployees']);

    Route::get('/chart-of-employees', [EmployeeChartController::class, 'chartOfEmployees']);

    Route::get('attendance/reports', [ReportController::class, 'attendance']);
    Route::get('attendance/reports/summary', [ReportController::class, 'summary_report']);
    Route::get('reports/late', [ReportController::class, 'late']);
    Route::get('reports/absent', [ReportController::class, 'absent']);
    Route::get('reports/emp-attendance', [ReportController::class, 'emp_attendance']);

    // Notification
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/vanish/{id}', [NotificationController::class, 'vanishSingle']);
    Route::get('/notifications/show/{id}', [NotificationController::class, 'show']);
    Route::get('/my_notification', [NotificationController::class, 'my_notification']);
    Route::get('/notifications/all-deleted', [NotificationController::class, 'vanishAll']);

    // Approval approved
    Route::post('/approvals/approve', [ApprovalController::class, 'approved']);
    Route::post('/approvals/update-remarks', [ApprovalController::class, 'updateRemarks']);

    // Attendance Request
    Route::get('/attendance/requests', [AttendanceRequestController::class, 'index']);
    Route::post('/attendance/requests', [AttendanceRequestController::class, 'store']);
    Route::get('/attendance/requests/{id}', [AttendanceRequestController::class, 'show']);
    Route::get('/app_settings', [HomeController::class, 'app_settings']);

    Route::get('/my-roster', [RosterController::class, 'my_roster']);

    Route::get('/admin/notices', [NoticeController::class, 'index']);
    Route::post('/admin/notices', [NoticeController::class, 'store']);
    Route::get('/admin/notices/{id}', [NoticeController::class, 'show']);
    Route::post('/admin/notices/{id}/delete', [NoticeController::class, 'delete']);
    Route::post('/admin/notices/update/{id}', [NoticeController::class, 'update']);


    Route::post('data-query-request', [DataQueryRequestController::class, 'store']);
    Route::get('data-query-request', [DataQueryRequestController::class, 'index']);
    Route::post('data-query-request/answer', [DataQueryRequestController::class, 'answer']);

    Route::post('leaves/get-duration', [ControllersLeaveApplicationController::class, 'get_duration']);
    Route::post('leaves/delete/{id}', [ControllersLeaveApplicationController::class, 'delete']);

    Route::post('user/firebase-token', [HomeController::class, 'updateFirebaseToken']);

    // Payroll
    Route::get('payroll/cards', [PayrollController::class, 'cards']);
    Route::get('payroll/provident-funds', [PayrollController::class, 'provident_funds']);
    Route::get('payroll/payslips', [PayrollController::class, 'payslips']);
});
