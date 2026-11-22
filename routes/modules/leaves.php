<?php

use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\StationLeaveApplicationController;
use Illuminate\Support\Facades\Route;

Route::delete('leaves/{leave}', [LeaveApplicationController::class, 'destroy'])
    ->name('leaves.destroy');
Route::get('leaves/history', [LeaveApplicationController::class, 'history'])
    ->name('leaves.history');
Route::get('leaves/apply', [LeaveApplicationController::class, 'apply'])
    ->name('leaves.apply');
Route::post('leaves/apply', [LeaveApplicationController::class, 'apply_save'])
    ->name('leaves.apply.save');
Route::get('leaves/all/pending', [LeaveApplicationController::class, 'all'])
    ->name('leaves.all');
Route::get('leaves/all/approve', [LeaveApplicationController::class, 'all_approve'])
    ->name('leaves.all.approve');
Route::get('leaves/all/rejected', [LeaveApplicationController::class, 'all_rejected'])
    ->name('leaves.all.rejected');
Route::get('leaves-types', [LeaveApplicationController::class, 'types'])
    ->name('leaves.types');
Route::delete('leaves-types/delete', [LeaveApplicationController::class, 'types_delete'])
    ->name('leaves.types.delete');
Route::post('leaves-types', [LeaveApplicationController::class, 'types_save'])
    ->name('leaves.types.save');
Route::post('leaves-types/update', [LeaveApplicationController::class, 'types_update'])
    ->name('leaves.types.update');
Route::get('leaves/balance', [LeaveApplicationController::class, 'balance'])
    ->name('leaves.balance');
Route::get('leaves/balance/employee', [LeaveApplicationController::class, 'balance_employee'])
    ->name('leaves.balance.employee');
Route::post('leaves/balance', [LeaveApplicationController::class, 'balance_update_default'])
    ->name('leaves.balance.update.default');
Route::post('leaves/balance/{user_id}', [LeaveApplicationController::class, 'balance_update'])
    ->name('leaves.balance.update');
Route::post('leaves/{id}/update-type', [LeaveApplicationController::class, 'update_leave_type'])
    ->name('leaves.update-type');
Route::get('leaves/{id}/approved-history', [LeaveApplicationController::class, 'approved_history'])
    ->name('leaves.approved-history');
Route::get('leaves/{id}', [LeaveApplicationController::class, 'show'])    ->name('leaves.show');
Route::post('leaves/approve/{id}', [LeaveApplicationController::class, 'approve'])
    ->name('leaves.approve');
Route::get('leaves/reject/{id}', [LeaveApplicationController::class, 'reject'])
    ->name('leaves.reject');
Route::post('leaves/approve/{id}/final', [LeaveApplicationController::class, 'approve_final'])
    ->name('leaves.approve.final');
Route::get('leaves/reject/{id}/final', [LeaveApplicationController::class, 'reject_final'])
    ->name('leaves.reject.final');
Route::post('leaves/approve/{id}/second', [LeaveApplicationController::class, 'approve_second'])
    ->name('leaves.approve.second');
Route::get('leaves/reject/{id}/second', [LeaveApplicationController::class, 'reject_second'])
    ->name('leaves.reject.second');

Route::get('leaves/print/{id}', [LeaveApplicationController::class, 'print'])->name('leaves.print');
Route::get('leaves/download/{id}', [LeaveApplicationController::class, 'download'])->name('leaves.download');

Route::get('emp-leave-report', [LeaveApplicationController::class, 'emp_leave_report'])->name('leaves.emp_leave_report');
Route::post('leaves/get-duration', [LeaveApplicationController::class, 'get_duration'])->name('leaves.get_duration');
Route::get('leaves/earn-leave/history', [LeaveApplicationController::class, 'earn_leave_history'])->name('leaves.earn_leave_history');
Route::get('leaves/earn-leave/policy', [LeaveApplicationController::class, 'earn_leave_policy'])->name('leaves.earn_leave.policy');

//---------------Station Leave----------------------

Route::delete('station/leave/{leave}', [StationLeaveApplicationController::class, 'destroy'])
    ->name('station.leave.destroy');
Route::get('station/leave/history', [StationLeaveApplicationController::class, 'history'])
    ->name('station.leave.history');

Route::get('station/leave/apply', [StationLeaveApplicationController::class, 'apply'])
    ->name('station.leave.apply');
Route::get('station/leave/pending', [StationLeaveApplicationController::class, 'all'])
    ->name('station.leave.pending');

Route::post('station/leave/apply', [StationLeaveApplicationController::class, 'apply_save'])
    ->name('station.leave.apply.save');

Route::get('station/leave/all/approve', [StationLeaveApplicationController::class, 'all_approve'])
    ->name('station.leave.all.approve');
Route::get('station/leave/all/rejected', [StationLeaveApplicationController::class, 'all_rejected'])
    ->name('station.leave.all.rejected');

Route::get('station/leave/{id}', [StationLeaveApplicationController::class, 'show'])    ->name('station.leave.show');
Route::post('station/leave/approve/{id}', [StationLeaveApplicationController::class, 'approve'])
    ->name('station.leave.approve');
Route::get('station/leave/reject/{id}', [StationLeaveApplicationController::class, 'reject'])
    ->name('station.leave.reject');
Route::post('station/leave/approve/{id}/final', [StationLeaveApplicationController::class, 'approve_final'])
    ->name('station.leave.approve.final');
Route::get('station/leave/reject/{id}/final', [StationLeaveApplicationController::class, 'reject_final'])
    ->name('station.leave.reject.final');
Route::post('station/leave/approve/{id}/second', [StationLeaveApplicationController::class, 'approve_second'])
    ->name('station.leave.approve.second');
Route::get('station/leave/reject/{id}/second', [StationLeaveApplicationController::class, 'reject_second'])
    ->name('station.leave.reject.second');

Route::get('station/leaves/print/{id}', [StationLeaveApplicationController::class, 'print'])->name('station.leave.print');
Route::get('station/leaves/download/{id}', [StationLeaveApplicationController::class, 'download'])->name('station.leave.download');






