<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\EmployeeWeeklyScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('zz_marker_xyz', function () {
    return 'ok';
})->name('zz.marker');

Route::get('employee/list', [EmployeeController::class, 'list'])->name('employees.active.list');
Route::post('employee/save', [EmployeeController::class, 'save'])->name('employees.active.save');
Route::get('employee/view/{id}', [EmployeeController::class, 'show'])->name('employees.active.show');
Route::get('employee/create', [EmployeeController::class, 'create'])->name('employees.active.create');
Route::get('employee/{id}/print', [EmployeeController::class, 'print'])->name('employees.active.print');
Route::get('employee/search', [EmployeeController::class, 'search'])->name('employees.active.search.all');
Route::get('employee/profile/{id}', [EmployeeController::class, 'profile'])->name('employees.active.profile');
Route::post('employee/terminate', [EmployeeController::class, 'terminate'])->name('employees.active.terminate');
Route::put('employee/update/{employee}', [EmployeeController::class, 'update'])->name('employees.active.update');
Route::get('employee/view/edit/{employee_id}', [EmployeeController::class, 'edit'])->name('employees.active.edit');
Route::delete('employee/delete/{employee}', [EmployeeController::class, 'delete'])->name('employees.active.delete');
Route::get('employee/delete-fingerprint/{fingerprint}', [EmployeeController::class, 'delete_fingerprint'])->name('employees.active.delete-fingerprint');

// Removed 2026-07-15 (incident recovery): EmployeeController::employeesTabs() does not exist
// (BadMethodCallException -> HTTP 500) and no view/menu references route 'employees.tabs.list'.
// The employee tab bar is the 'tabs.employees' Blade partial, which links to existing routes.
// Re-add with a real controller method + view if a dedicated tabs page is ever needed.
// Route::get('employee/employees-tabs', [EmployeeController::class, 'employeesTabs'])->name('employees.tabs.list');

Route::get('employee/terminate/list', [EmployeeController::class, 'list_terminated'])->name('employees.terminate.list');

Route::post('employee/request', [EmployeeController::class, 'edit_request'])->name('employees.request.edit');
Route::get('employee/request-edit', [EmployeeController::class, 'show_edit_request'])->name('employees.request.edit.show');
Route::get('employee/request-edit/view/{id}', [EmployeeController::class, 'view_edit_request'])->name('employees.request.edit.view');
Route::delete('employee/request-edit/{id}', [EmployeeController::class, 'destroy_edit_request'])->name('employees.request.edit.delete');
Route::post('employee/request-edit/primary-approve/{id}', [EmployeeController::class, 'primary_approve'])->name('employees.request.edit.primary.approve');
Route::get('employee/request-edit/approve/view/{id}', [EmployeeController::class, 'view_approve_edit_request'])->name('employees.request.edit.approve.view');
Route::post('employee/request-edit/final-approve/{id}', [EmployeeController::class, 'final_approve'])->name('employees.request.edit.final.approve');

Route::get('employee/probation/list', [EmployeeController::class, 'probationList'])->name('employees.probation.list');

Route::get('employee/designations', [EmployeeController::class, 'designationsIndex'])->name('employees.designations.index');
Route::post('employee/designations/save', [EmployeeController::class, 'saveRecordDesignations'])->name('employees.designations.save');
Route::post('employee/designations/update', [EmployeeController::class, 'updateRecordDesignations'])->name('employees.designations.update');
Route::post('employee/designations/update-order', [EmployeeController::class, 'updateDesignationOrder'])->name('employees.designations.update-order');
Route::delete('employee/designations/delete', [EmployeeController::class, 'deleteRecordDesignations'])->name('employees.designations.delete');
Route::post('employee/designations/modal/save', [EmployeeController::class, 'saveModalDesignations'])->name('employees.designations.modal.save');

Route::get('employee/interview', [InterviewController::class, 'interviewEmployees'])->name('employees.interview.index');
Route::get('employee/interview/create', [InterviewController::class, 'create'])->name('employees.interview.create');
Route::post('employee/interview/store', [InterviewController::class, 'store'])->name('employees.interview.store');
Route::get('employee/interview/view/{id}', [InterviewController::class, 'show'])->name('employees.interview.show');
Route::get('employee/interview/edit/{id}', [InterviewController::class, 'edit'])->name('employees.interview.edit');
Route::put('employee/interview/update', [InterviewController::class, 'update'])->name('employees.interview.update');
Route::delete('employee/interview/delete/{id}', [InterviewController::class, 'delete'])->name('employees.interview.delete');
Route::post('employee/interview/active', [InterviewController::class, 'activeEmployee'])->name('employees.interview.active');
Route::post('employee/interview/probation', [InterviewController::class, 'probationEmployee'])->name('employees.interview.probation');
Route::post('employee/interview/comment', [InterviewController::class, 'CommentStore'])->name('employees.interview.comment');
Route::get('employee/interview/comment/delete/{id}', [InterviewController::class, 'CommentDestroy'])->name('employees.interview.comment.delete');

Route::get('employee/weekly-schedules', [EmployeeWeeklyScheduleController::class, 'index'])->name('employees.weekly-schedules.index');
Route::get('employee/weekly-schedules/{employee}/edit', [EmployeeWeeklyScheduleController::class, 'edit'])->name('employees.weekly-schedules.edit');
Route::put('employee/weekly-schedules/{employee}/update', [EmployeeWeeklyScheduleController::class, 'update'])->name('employees.weekly-schedules.update');
Route::delete('employee/weekly-schedules/{schedule}/destroy', [EmployeeWeeklyScheduleController::class, 'destroy'])->name('employees.weekly-schedules.destroy');
Route::patch('employee/weekly-schedules/{schedule}/toggle-status', [EmployeeWeeklyScheduleController::class, 'toggleStatus'])->name('employees.weekly-schedules.toggle-status');
Route::get('employee/weekly-schedules/api/employees', [EmployeeWeeklyScheduleController::class, 'getEmployeesSchedules'])->name('employees.weekly-schedules.api.employees');

Route::get('employee/my-schedule', [EmployeeWeeklyScheduleController::class, 'show'])->name('employees.weekly-schedules.show');
Route::get('employee/api/today-locations', [EmployeeWeeklyScheduleController::class, 'getTodayEligibleLocations'])->name('employees.api.today-locations');
Route::get('employee/ajax/employee/{id}', [EmployeeController::class, 'employeeByID'])->name('employees.ajax.employee.employeeByID');
Route::get('employee/ajax/check-employee-id', [EmployeeController::class, 'checkEmployeeIdAvailability'])->name('employees.ajax.check.employeeId');
