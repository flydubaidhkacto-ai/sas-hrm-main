<?php

use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

//SMS Controller
Route::get('/sms/api-usage', [SmsController::class, 'api_usage'])->name('sms.api.usage');
Route::get('/sms/bulk', [SmsController::class, 'bulk'])->name('sms.bulk');
Route::get('/sms/employee/list', [SmsController::class, 'list'])->name('sms.employee.list');
Route::post('/sms/employee/send', [SmsController::class, 'send_employee'])->name('sms.employee.send');
Route::post('/sms/message/send', [SmsController::class, 'send_message'])->name('sms.message.send');
Route::post('/sms/format/change', [SmsController::class, 'showForm'])->name('sms.format.send');
Route::get('/sms/format/list', [SmsController::class, 'sms_format_list'])->name('sms.format.list');
Route::get('/sms/format/fine', [SmsController::class, 'fine_format'])->name('finemessage.formate');
Route::get('/sms/format/warning', [SmsController::class, 'warning_format'])->name('warningmessage.formate');
Route::post('/sms/warning_format/save', [SmsController::class, 'warning_format_store'])->name('warning.format.store');
Route::get('/sms/format/leav_appliccation', [SmsController::class, 'leav_appliccation_format'])->name('leavmessage.formate');
Route::post('/sms/leave_application_format/save', [SmsController::class, 'leave_application_format_store'])->name('leave_application.format.store');
Route::get('/sms/format/loan_application', [SmsController::class, 'loan_application_format'])->name('loan_application.formate');
Route::post('/sms/loan_application_format/save', [SmsController::class, 'loan_application_format_store'])->name('loan_application.format.store');
Route::get('/sms/format/promotion', [SmsController::class, 'promotion_format'])->name('promotion.formate');
Route::post('/sms/promotion_format/save', [SmsController::class, 'promotion_format_store'])->name('promotion_format.format.store');

Route::get('/sms/format/rewards', [SmsController::class, 'rewards_format'])->name('rewards.formate');
Route::post('/sms/rewards_format/save', [SmsController::class, 'rewards_format_store'])->name('rewards_format.format.store');

Route::get('/sms/format/student_check_in', [SmsController::class, 'student_check_in_format'])->name('student_check_in.formate');
Route::post('/sms/student_check_in_format/save', [SmsController::class, 'student_check_in_format_store'])->name('student_check_in_format.format.store');
Route::get('/sms/format/student_check_out', [SmsController::class, 'student_check_out_format'])->name('student_check_out.formate');
Route::post('/sms/student_check_out_format/save', [SmsController::class, 'student_check_out_format_store'])->name('student_check_out_format.format.store');


Route::get('sms/history', [SmsController::class, 'history'])->name('sms.history');
Route::get('sms/api', [SmsController::class, 'api'])->name('sms.api');
Route::post('sms/api/save', [SmsController::class, 'api_save'])->name('sms.api.save');

