<?php

use App\Classes\ApiResponseClass;
use App\Gates\Attendance;
use App\Http\Controllers\Api\Select2Controller;
use App\Http\Controllers\Api\SystemApiController;
use App\Http\Controllers\AppApiController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveApplicationController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\Api\LiveLocationController;
use App\Http\Controllers\Api\MobileApiAuthController;
use App\Http\Controllers\SuperDepartmentController;
use App\Http\Middleware\ApiAuthorizedRequest;
use App\Http\Middleware\ForceLogoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('system')
    ->group(function () {
        Route::post('update-license', [SystemApiController::class, 'update_license']);
        Route::post('get-code-version', [SystemApiController::class, 'get_code_version']);
        Route::post('update-code', [SystemApiController::class, 'update_code']);
    });

Route::middleware([
    ApiAuthorizedRequest::class,
])
    ->prefix('app')
    ->group(function () {
        Route::post('/test', fn() => ['test' => 'test', 'client' => request()->client]);
        Route::get('/users', [AppApiController::class, 'get_users']);
        Route::post('/attendances', [AppApiController::class, 'attendances']);
        Route::post('/v2-client', [AppApiController::class, 'v2_client']);
    });

Route::get('super-departments/select2', [Select2Controller::class, 'select2_super_departments'])->name('super_departments.select2');
Route::get('departments/select2', [Select2Controller::class, 'select2_departments'])->name('departments.select2');
Route::get('designations/select2', [Select2Controller::class, 'select2_designations'])->name('designations.select2');
Route::get('employees/select2', [Select2Controller::class, 'select2_employees'])->name('employees.select2');
Route::get('students/select2', [Select2Controller::class, 'select2_students'])->name('students.select2');

Route::middleware(['auth', ForceLogoutMiddleware::class])->group(function () {
    Route::get('shifts', [ShiftController::class, 'shifts']);
    Route::post('shifts', [ShiftController::class, 'api_create']);

    Route::prefix('roster/shifts')->group(function () {
        Route::get('/', [RosterController::class, 'shifts']);
        Route::post('/', [RosterController::class, 'shifts_store']);
        Route::put('/{shift}', [RosterController::class, 'shifts_update']);
        Route::delete('/{shift}', [RosterController::class, 'shifts_delete']);
    });
});
