<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::controller(AttendanceController::class)->prefix('outside-duty')->name('attendence.')->group(function () {
    Route::get('/', 'outside_duty')->name('outside-duty');
    Route::post('/entry', 'outSideEntry')->name('outside.entry');
    Route::post('/out', 'outSideOut')->name('outside.out');
    Route::get('checking', 'check_employee_schedule')->name('check_employee_schedule');
});
