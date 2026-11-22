<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/attendance/shift/update/{attendance}', [AttendanceController::class, 'attendance_shift_update'])->name('attendance.shift.update');
Route::get('/attendance/gracetime', [AttendanceController::class, 'gracetime'])->name('attendance.gracetime');
Route::get('/attendance/edit', [ReportController::class, 'attendance'])->name('attendance.editall');
Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
Route::get('attendance/entry', [AttendanceController::class, 'entry'])->name('attendance.entry');
Route::post('attendance/entry', [AttendanceController::class, 'entry_save'])->name('attendance.entry.store');
Route::get('attendance/edit/{id}', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::put('attendance/edit/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

Route::post('attendance/overtime-schedule/date', [AttendanceController::class, 'overtime_schedule_date_all_overtime']);
Route::get('attendance/overtime-schedule/date', [AttendanceController::class, 'overtime_schedule_date'])
    ->name('attendance.overtime.schedule_date');
Route::post('attendance/overtime-schedule/date/update-default-limit', [AttendanceController::class, 'overtime_schedule_date_update_default_limit'])
    ->name('attendance.overtime.schedule_date.update-default-limit');
Route::post('attendance/overtime-schedule/date/update-limit/{user_id}/{date}', [AttendanceController::class, 'overtime_schedule_date_update_limit'])
    ->name('attendance.overtime.schedule_date.update-limit');
Route::post('attendance/overtime-schedule/date/{id}', [AttendanceController::class, 'overtime_schedule_date_save'])
    ->name('attendance.overtime.schedule_date.save');
Route::post('attendance/overtime-schedule/date/{id}', [AttendanceController::class, 'overtime_schedule_date_save'])
    ->name('attendance.overtime.schedule_date.save');

Route::get('attendance/overtime/schedule', [AttendanceController::class, 'overtime_schedule'])
    ->name('attendance.overtime.schedule');
Route::post('attendance/overtime/schedule', [AttendanceController::class, 'overtime_schedule_all_overtime']);
Route::post('attendance/overtime/schedule/update-default-limit', [AttendanceController::class, 'overtime_schedule_update_default_limit'])
    ->name('attendance.overtime.schedule.update-default-limit');
Route::post('attendance/overtime/schedule/update-limit/{user_id}/{date}', [AttendanceController::class, 'overtime_schedule_update_limit'])
    ->name('attendance.overtime.schedule.update-limit');
Route::post('attendance/overtime/schedule/{id}', [AttendanceController::class, 'overtime_schedule_save'])
    ->name('attendance.overtime.schedule.save');
Route::post('attendance/overtime/schedule/{id}', [AttendanceController::class, 'overtime_schedule_save'])
    ->name('attendance.overtime.schedule.save');

Route::get('attendance/note', [AttendanceController::class, 'note'])->name('attendance.note');
Route::post('attendance/note/{id}', [AttendanceController::class, 'note_save'])->name('attendance.note.save');


Route::get('attendance-req-work-home', [AttendanceRequestController::class, 'work_from_home_req'])
    ->name('attendance-requests-work-home');
Route::post('attendance-req-work-home', [AttendanceRequestController::class, 'work_from_home_req_save']);

Route::get('attendance-requests', [AttendanceRequestController::class, 'index'])->name('attendance-requests');
Route::get('attendance-requests/create', [AttendanceRequestController::class, 'create'])->name('attendance-requests.create');
Route::post('attendance-requests/create', [AttendanceRequestController::class, 'preview'])
    ->name('attendance-requests.preview');
Route::delete('attendance-requests/delete/{id}', [AttendanceRequestController::class, 'destroy'])
    ->name('attendance-requests.delete');
Route::post('attendance-requests/decide/approve/{id}', [AttendanceRequestController::class, 'approve'])
    ->name('attendance-requests.approve');

Route::post('attendance/manual/store', [AttendanceController::class, 'manual_store'])->name('attendance.manual.store');
Route::post('attendance/update/out-time', [AttendanceController::class, 'update_out_time'])->name('attendance.update.out-time');
