<?php

use App\Http\Controllers\Api\MobileApi\LiveLocationController;
use App\Http\Controllers\ApiKeysController;
use App\Http\Controllers\ApprovalController;
use App\Http\Middleware\ForceLogoutMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController as AuthResetPasswordController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftScheduleController;
use App\Http\Controllers\SuperDepartmentController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DataQueryRequestController;
use App\Http\Controllers\GenerateOfficialFormController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LocationPunchController;
use App\Http\Controllers\OfficialFormController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\UserActivityLogController;
use App\Http\Controllers\ZkTecoController;
use App\Http\Controllers\MobileAppController;
use App\Http\Controllers\TestController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Brian2694\Toastr\Facades\Toastr;

use Illuminate\Support\Facades\URL;

Route::redirect('/', '/login');

// Debug/Test routes - only loaded in non-production environments
// These prevent route caching in production, which is correct behavior
if (!app()->environment('production')) {
    Route::get('/debug-auth', function () {
        return [
            'auth' => auth()->check(),
            'user' => auth()->user(),
            'session_id' => session()->getId(),
        ];
    })->middleware('auth');

    // Temporary debug route to inspect URL generation and environment
    Route::get('/__local_debug', function () {
        return [
            'generated_url' => url('/'),
            'app_url' => config('app.url'),
            'env' => app()->environment(),
            'request_scheme' => request()->getScheme(),
            'url_to' => URL::to('/'),
            'getenv_APP_ENV' => getenv('APP_ENV'),
            'env_helper' => env('APP_ENV'),
            'server_APP_ENV' => $_SERVER['APP_ENV'] ?? null,
            'env_array' => array_intersect_key($_ENV, ['APP_ENV'=>1,'APP_URL'=>1]),
        ];
    });

    Route::get('/debug-role', function () {
        return [
            'has_module_payroll' => has_module('payroll'),
            'user' => auth()->id(),
            'role' => auth()->user()->assinged_role?->name,
            'permissions' => auth()->user()->assinged_role?->permissions,
            'can' => role_can('payslip.show'),
        ];
    })->middleware('auth');

    Route::get('test-email', function (Request $request) {
        if ($request->email) {
            try {
                Mail::raw('This is a test email', function ($message) use ($request) {
                    $message->to($request->email)->subject('Test Email');
                });
            } catch (\Exception $e) {
                dump($e);
            }
        }
    });
}

Route::middleware('guest')->group(function () {
    // -----------------------------login----------------------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate')->name('login.post');
    });
    // ------------------------------ register ---------------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'storeUser');
    });

    // ----------------------------- forget password ----------------------------//
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forget-password', 'getEmail')->name('forget-password');
        Route::post('forget-password', 'postEmail');
    });

    // --------- reset password ----------//
    Route::controller(AuthResetPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'getPassword')->name('password.reset');
        Route::post('reset-password', 'updatePassword')->name('password.update');
    });
});

// Mobile App Download Routes (Public access)
Route::get('/mobile-app', [MobileAppController::class, 'index'])->name('mobile-app.download');
Route::get('/mobile-app/download', [MobileAppController::class, 'downloadAPK'])->name('mobile-app.apk');

// Mobile App QR Authentication Routes (Authenticated users only)
Route::middleware('auth')->group(function () {
    Route::get('/mobile-app/generate-qr', [MobileAppController::class, 'generateAuthQR'])->name('mobile-app.generate-qr');
    Route::get('/mobile-app/qr-status', [MobileAppController::class, 'checkQRStatus'])->name('mobile-app.qr-status');
});

Route::middleware(['auth', ForceLogoutMiddleware::class])->group(function () {

    Route::view('/get-more', 'get_more')->name('get-more');

    Route::get('/logout',  [LoginController::class, 'logout'])->name('logout');
    Route::get('/password/change',  [HomeController::class, 'password_change'])->name('password.change');
    Route::post('/password/change',  [HomeController::class, 'password_change_save']);

    // Device Session Management Routes
    Route::prefix('device-sessions')->name('device-sessions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DeviceSessionController::class, 'index'])->name('index');
        Route::get('/get', [\App\Http\Controllers\DeviceSessionController::class, 'getActiveSessions'])->name('get');
        Route::post('/terminate', [\App\Http\Controllers\DeviceSessionController::class, 'terminate'])->name('terminate');
        Route::post('/terminate-others', [\App\Http\Controllers\DeviceSessionController::class, 'terminateOthers'])->name('terminate-others');
    });

    Route::get('/test', [TestController::class, 'test']);
    // ----------------------------- main dashboard ------------------------------//
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home');
        Route::get('/departments/{department}', 'department_current_status')->name('department.current-present');

        // AJAX Dashboard endpoints
        Route::get('/ajax/employee-stats', 'getEmployeeStats')->name('ajax.employee-stats');
        Route::get('/ajax/machine-punches', 'getMachinePunches')->name('ajax.machine-punches');
        Route::get('/ajax/branch-stats', 'getBranchStats')->name('ajax.branch-stats');
        Route::get('/ajax/pending-tasks', 'getPendingTasks')->name('ajax.pending-tasks');
        Route::get('/ajax/admin-cards', 'getAdminCards')->name('ajax.admin-cards');
    });

    // -----------------------------settings----------------------------------------//
    Route::controller(SettingController::class)->group(function () {
        Route::get('company/settings', 'companySettings')->name('company.info');
        Route::post('company/settings', 'companySettingsSave')->name('company.info.save');
    });
});

Route::middleware([
    'auth',
    ForceLogoutMiddleware::class,
])
    ->group(function () {
        // ----------------------------- user profile ------------------------------//
        Route::controller(UserManagementController::class)
            ->prefix('users')
            ->name('users.')
            ->group(function () {
                Route::get('profile', 'profile')->name('profile');
                Route::get('/', 'users')->name('index');
                Route::get('/{user}', 'show')->name('show');
                Route::get('/edit/{user}', 'edit')->name('edit');
                Route::delete('/delete/{user}', 'destroy')->name('destroy');
            });

        // ----------------------------- form employee ------------------------------//
        Route::controller(SuperDepartmentController::class)->group(function () {
            Route::get('/departments', 'index')->name('departments.index');
            Route::get('/departments/create', 'create')->name('departments.create');
            Route::post('/departments/save', 'save')->name('departments.save');
            Route::post('/departments/update', 'update')->name('departments.update');
            Route::get('/departments/{id}', 'department')->name('departments.departments');
            Route::delete('/department/delete', 'delete')->name('departments.delete');

            Route::post('/departments/departments/save/', 'save_sub_department')->name('departments.department.save');
            Route::post('/department/departments', 'updateRecordDepartment')->name('departments.department.update');
            Route::delete('/department/departments/delete', 'deleteRecordDepartment')->name('departments.department.delete');

            Route::post('/departments/departments-save/', 'departmentModalSave')->name('departments.modal.save');
            Route::post('/departments/update-order', 'updateDepartmentOrder')->name('departments.update-order');
            Route::post('/departments/super-department/update-order', 'updateSuperDepartmentOrder')->name('departments.super-department.update-order');
        });


        Route::controller(HolidayController::class)->group(function () {
            Route::get('/holidays', 'holiday')->name('holidays');
            Route::get('/holidays/range', 'holidayRange')->name('holidays.range');
            Route::post('/holidays/save', 'saveRecord')->name('holidays.save');
            Route::post('/holidays/range/save', 'saveRangeRecord')->name('holidayRange.save');
            Route::put('/holidays/update', 'updateRecord')->name('holidays.update');
            Route::put('/holidays/range/update', 'updateRangeRecord')->name('holidaysRange.update');
            Route::delete('/holidays', 'delete')->name('holidays.delete');
        });

        Route::get('/location-punch/checkin', [LocationPunchController::class, 'checkin'])->name('locations.checkin');
        Route::post('/location-punch/punch', [LocationPunchController::class, 'punch'])->name('locations.punch');
        Route::get('/locati-punch/checkin/report', [LocationPunchController::class, 'checkin_report'])->name('locations.checkin.report');

        // Location management routes
        Route::controller(LocationController::class)->group(function () {
            Route::get('/locations', 'index')->name('locations.index');
            Route::post('/locations/store', 'store')->name('locations.store');
            Route::put('/locations/update', 'update')->name('locations.update');
            Route::delete('/locations/destroy', 'destroy')->name('locations.destroy');
            Route::get('/locations/{id}', 'show')->name('locations.show');
            Route::get('/api/locations/hierarchy', 'getHierarchy')->name('locations.hierarchy');
            Route::get('/api/locations/parent/{parentId?}', 'getByParent')->name('locations.by-parent');
            Route::get('/api/locations/{id}/coordinates', 'getCoordinates')->name('locations.coordinates');
            Route::post('/api/locations/search-attendance', 'searchForAttendance')->name('locations.search-attendance');
        });

        // shift scheduling routes
        Route::prefix('/scheduling/shifts')->name('scheduling.')->controller(ShiftScheduleController::class)->group(function () {
            Route::get('/', 'index')->name('shifts.index');
            Route::get('/create', 'create')->name('shifts.create');
            Route::post('/store', 'store')->name('shifts.store');
            Route::get('/delete/{id}', 'delete')->name('shifts.delete');
        });

        // shift related routes
        Route::prefix('shifts')->name('shifts.')->controller(ShiftController::class)->group(function () {
            Route::get('/', 'shifts')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{shift}', 'destroy')->name('destroy');
            Route::get('/assign/{shift}', 'assign')->name('assign');
            Route::post('/assign/{shift}/save', 'assign_save')->name('assign.save');
        });
        Route::get('student-shifts', [ShiftController::class, 'shifts'])->name('student_shifts');


        Route::controller(ReportController::class)->group(function () {
            Route::get('/reports/machine-log', 'machine_log')->name('reports.machine-log');
            Route::get('/reports/in-out-summery', 'in_out_summery')->name('reports.in-out');
            Route::get('/reports/attendance', 'attendance')->name('reports.attendance');
            Route::get('/reports/missed-attendance', 'missed_attendance')->name('reports.missed-attendance');

            Route::get('/reports/employee-attendance', 'attendance_employee')->name('reports.attendance.employee');
            Route::get('/reports/late', 'late')->name('reports.late');
            Route::get('/reports/absent', 'absent')->name('reports.absent');
            Route::get('/reports/long-absent', 'absent_long')->name('reports.absent.long');
            Route::get('/reports/total-duty-report', 'total_duty_report')->name('reports.total_duty_report');
            Route::get('/reports/total-duty-report/generate', 'total_duty_report_generate')->name('reports.total_duty_report.generate');
            Route::post('/reports/total-duty-report/create_view', 'total_duty_report_create_view')->name('reports.total_duty_report.create_view');

            Route::get('/regenerate/attendance/report', 'regenerate_attendance_report')->name('regenerate.attendance.report');

            if (has_module('live-location-tracking')) {
                Route::get('/reports/live-location', 'live_location')->name('reports.live-location');
            }

            Route::get('/reports/attendanceExportReportTest', 'attendanceExportReportTest')->name('reports.attendanceExportReportTest');
            Route::get('/reports/payrollDemoReport', 'payrollDemoReport')->name('reports.payrollDemoReport');
        });

        Route::get('api-keys', [ApiKeysController::class, 'index'])->name('api-keys');
        Route::post('api-keys', [ApiKeysController::class, 'create'])->name('api-keys.create');
        Route::delete('api-keys/{id}', [ApiKeysController::class, 'delete'])->name('api-keys.delete');
        Route::post('/api-keys/clients/assign-department/{client}', [ApiKeysController::class, 'assign_department'])->name('api-keys.assign-departments');
        Route::post('api-keys/clients/block/{client}', [ApiKeysController::class, 'block_client'])->name('api-keys.block-client');

        Route::get('zkteco-api', [ZkTecoController::class, 'index'])->name('zkteco');
        Route::post('zkteco-api', [ZkTecoController::class, 'create'])->name('zkteco.create');
        Route::delete('zkteco-api/{id}', [ZkTecoController::class, 'delete'])->name('zkteco.delete');
        Route::post('zkteco-api/devices/assign-department/{device}', [ZkTecoController::class, 'assign_department'])
            ->name('zkteco.assign-departments');
        Route::delete(
            'zkteco-api/devices/{device}',
            [ZkTecoController::class, 'delete_device']
        )->name('zkteco.delete-device');

        Route::resource('role', RoleController::class);

        // Route::get('settings/attendance', [SettingController::class, 'attendance'])->name('settings.attendance');
        // Route::post('settings/attendance', [SettingController::class, 'attendance_update'])->name('settings.attendance.update');
        Route::post('settings/late/min_late_minutes', [SettingController::class, 'min_late_minutes'])->name('settings.min_late_minutes');
        Route::get('settings/attendance', [SettingController::class, 'attendance'])->name('settings.attendance');
        Route::get('settings/attendance/location', [SettingController::class, 'attendance_location'])
            ->name('settings.attendance.location');
        Route::post('settings/attendance', [SettingController::class, 'attendance_update'])->name('settings.attendance.update');

        Route::get('settings/interests', [SettingController::class, 'interests'])->name('settings.interests');
        Route::post('settings/interests', [SettingController::class, 'interests_update'])->name('settings.interests.update');


        Route::get('settings/ui', [SettingController::class, 'uiSettingInfo'])->name('ui.setting.info');
        Route::post('settings/ui-update', [SettingController::class, 'uiSettingUpdate'])->name('ui.setting.update');
        Route::get('settings/ui-reset', [SettingController::class, 'uiSettingDelete'])->name('ui.setting.delete');

        // Activity log
        Route::resource('activity-log', UserActivityLogController::class);

        // Guest Management Routes
        Route::controller(\App\Http\Controllers\GuestController::class)
            ->prefix('guests')->name('guests.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{id}', 'show')->name('show');
                Route::get('{id}/edit', 'edit')->name('edit');
                Route::put('{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('destroy');
                Route::post('{id}/toggle-status', 'toggleStatus')->name('toggle-status');
            });

        require_once __DIR__ . '/modules/employee.php';
        require_once __DIR__ . '/modules/attendance.php';
        require_once __DIR__ . '/modules/leaves.php';
        require_once __DIR__ . '/modules/notification.php';
        require_once __DIR__ . '/modules/promotion.php';
        require_once __DIR__ . '/modules/sms.php';


        $modules = [
            'student_attendance',
            'payroll',
            'outside_duty',
            'asset',
            'loans',
            'fines',
            'employee_performance',
            'employee_expenses',
            'roster',
            'inventory',
            'vehicle',
            'zkteco-cloud',
            'location-radius',
            'accounts',
            'works',
        ];
       foreach ($modules as $module) {
            $path = __DIR__ . "/modules/$module.php";

            if (file_exists($path)) {
                require $path;
            }
        }

        Route::get('/calendar', [CalendarController::class, 'get_data'])->name('calendar');

        Route::get('head-of-employees/{user}', [HomeController::class, 'head_of_employees'])->name('head-of-employees');
        Route::post('head-of-employees/{user}', [HomeController::class, 'head_of_employees_store']);
        Route::get('/get-weather', [HomeController::class, 'getWeather'])->name('weather');


        // billing for superadmin
        Route::get('billing', [BillingController::class, 'index'])->name('billing');
        Route::get('billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');

        Route::group([
            'prefix' => 'notice',
            'as' => 'notice.'
        ], function () {
            Route::get('/', [NoticeController::class, 'index'])->name('index');
            Route::get('/create', [NoticeController::class, 'create'])->name('create');
            Route::get('/show/{id}', [NoticeController::class, 'show'])->name('show');
            Route::get('/edit/{id}', [NoticeController::class, 'edit'])->name('edit');
            Route::post('/store', [NoticeController::class, 'store'])->name('store');
            Route::post('/update', [NoticeController::class, 'update'])->name('update');
            Route::delete('/delete', [NoticeController::class, 'delete'])->name('delete');
            Route::post('/viewed', [NoticeController::class, 'markAsViewed']);
            Route::get('/view', [NoticeController::class, 'view'])->name('view');
            Route::get('/viewers/{id}', [NoticeController::class, 'viewers'])->name('viewers');
        });

        // Backup route
        Route::group([
            'prefix' => 'backup',
            'as' => 'backup.'
        ], function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/create', [BackupController::class, 'create'])->name('create');
            Route::get('/download/{backup}', [BackupController::class, 'download'])->name('download');
            Route::delete('/destroy/{backup}', [BackupController::class, 'delete'])->name('destroy');
        });


        // Template route
        Route::group([
            'prefix' => 'letters',
            'as' => 'letters.'
        ], function () {
            Route::resource('template', TemplateController::class);
            Route::get('generate/download/{id}', [LetterController::class, 'download'])->name('generate.download');
            Route::resource('generate', LetterController::class);
        });

        Route::resource('official-form', OfficialFormController::class);
        Route::resource('official-form-generate', GenerateOfficialFormController::class);
        Route::view('employee-appraisal', 'employee_appraisal.index')->name('employee-appraisal.index');

        // Approval Process Routes
        Route::resource('approval-processes', \App\Http\Controllers\ApprovalProcessController::class);
        Route::post('approval-processes/{process}/add-step', [\App\Http\Controllers\ApprovalProcessController::class, 'addStep'])
            ->name('approval-processes.addStep');
        Route::delete('approval-processes/{process}/remove-step/{step}', [
            \App\Http\Controllers\ApprovalProcessController::class,
            'removeStep'
        ])->name('approval-processes.removeStep');

        Route::prefix('approvals')->name('approvals.')
            ->group(function () {
                // Route::get('{module}/{module_id}', [ApprovalController::class, 'show'])->name('show');
                Route::post('update-remarks', [ApprovalController::class, 'updateRemarks'])
                    ->name('update-remarks');
                Route::post('{module}/{module_id}/approve', [ApprovalController::class, 'approve'])->name('approve');
                Route::post('{module}/{module_id}/reject', [ApprovalController::class, 'reject'])->name('reject');
            });

            Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
            Route::get('/chat/messages/{receiverId}', [ChatController::class, 'getMessages'])->name('chat.messages');
            Route::post('/chat/send/{receiverId}', [ChatController::class, 'sendMessage'])->name('chat.send');
            Route::get('/audio-call', [ChatController::class, 'getAudioCall'])->name('chat.audioCall');
            Route::get('/video-call', [ChatController::class, 'getVideoCall'])->name('chat.videoCall');
            Route::post('/start-chat-or-group-chat', [ChatController::class, 'startChatOrGroupChat'])->name('chat.startChatOrGroupChat');
            Route::post('/chat/block/{receiverId}', [ChatController::class, 'block'])->name('chat.block');
            Route::post('/chat/unblock/{receiverId}', [ChatController::class, 'unblockUser'])->name('chat.unblock');

            // Cache clean - uses controller instead of closure for route caching compatibility
            Route::get('cache-clean', [HomeController::class, 'cacheClean'])->name('cache.clean');

        Route::post('data-query-request', [DataQueryRequestController::class, 'store'])->name('data.query.request.store');
        Route::get('data-query-request', [DataQueryRequestController::class, 'index'])->name('data.query.request.index');
        Route::post('data-query-request/answer', [DataQueryRequestController::class, 'answer'])->name('data.query.request.answer.store');

    });

Route::view('faq', 'faq')->name('faq');

Route::middleware(['auth', \App\Http\Middleware\ForceLogoutMiddleware::class])->prefix('api')->group(function () {
    Route::prefix('roster/shifts')->group(function () {
        Route::get('/', [\App\Http\Controllers\RosterController::class, 'shifts']);
        Route::post('/', [\App\Http\Controllers\RosterController::class, 'shifts_store']);
        Route::put('/{shift}', [\App\Http\Controllers\RosterController::class, 'shifts_update']);
        Route::delete('/{shift}', [\App\Http\Controllers\RosterController::class, 'shifts_delete']);
    });
});
