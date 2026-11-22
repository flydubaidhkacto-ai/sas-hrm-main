<?php

use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::delete('loans/{id}', [LoanController::class, 'destroy'])->name('loans.destroy');
Route::get('loans', [LoanController::class, 'loans'])->name('loans');
Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
Route::post('loans/apply', [LoanController::class, 'apply'])->name('loans.apply');

Route::get('loans/preview-payment-schedule', [LoanController::class, 'previewPaymentSchedule'])->name('loans.preview-payment-schedule');

Route::get('bulk-loans', [LoanController::class, 'bulkLoans'])->name('loans.bulk');
Route::post('bulk-loans/apply', [LoanController::class, 'bulkApply'])->name('loans.bulk.apply');