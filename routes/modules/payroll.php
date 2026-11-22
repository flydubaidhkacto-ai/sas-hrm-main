<?php

use App\Http\Controllers\BonusController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EarnHeadController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollApprovalController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\AutoRewardController;
use App\Http\Controllers\DutyBonusController;
use App\Http\Controllers\EmpBillController;
use App\Http\Controllers\EmployeeBillsCategoryController;
use App\Http\Controllers\PayslipPaymentController;
use Illuminate\Support\Facades\Route;



Route::controller(EarnHeadController::class)->prefix('earn-head')->name('earn-head.')->group(function () {
    Route::get('/', [EarnHeadController::class, 'index'])->name('index');
    Route::get('create', [EarnHeadController::class, 'create'])->name('create');
    Route::post('create', [EarnHeadController::class, 'store'])->name('store');
    Route::get('edit/{id}', [EarnHeadController::class, 'edit'])->name('edit');
    Route::put('update/{id}', [EarnHeadController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [EarnHeadController::class, 'delete'])->name('delete');
});

Route::controller(DeductionController::class)->prefix('deductions')->name('deductions.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('create', 'create')->name('create');
    Route::post('create', 'store')->name('store');
    Route::get('edit/{id}', 'edit')->name('edit');
    Route::put('update/{id}', 'update')->name('update');
    Route::delete('delete/{id}', 'delete')->name('delete');
});

Route::controller(DeductionController::class)->prefix('deduct-bulk')->name('deductions.')->group(function () {
    Route::get('bulk/{type}', 'bulk')->name('bulk');
    Route::post('bulk', 'bulkStore')->name('bulk.store');
});

Route::controller(PayrollController::class)
    ->prefix('payroll-cards')
    ->name('payroll-cards.')->group(function () {

    Route::get('/', 'payroll_cards')->name('index');
    Route::get('create/{user_id}', 'payroll_cards_create')->name('create');
    // Route::post('create/{user_id}', 'payroll_cards_store')->name('store');
    Route::put('update', 'payroll_cards_update')->name('update');
    Route::get('show/{id}', 'payroll_cards_show')->name('show');
    Route::get('edit/{id}', 'payroll_cards_edit')->name('edit');
});


Route::controller(PayslipController::class)->name('payslip.')->group(function () {
    Route::get('payslip/draft', 'draft')->name('draft');
    Route::get('payslip/ready-to-send', 'readyToSend')->name('ready_to_send');
    Route::get('payslip/approved', 'approved')->name('approved');
    Route::get('payslip/done', 'done')->name('done');
    Route::get('payslip/history', 'history')->name('history');
    Route::get('payslip/summary', 'summary')->name('summary');
    Route::get('payslip/paymentReport', 'paymentReport')->name('paymentReport');
    Route::get('/excel', 'excel')->name('excel');
    Route::get('/pdf', 'pdf')->name('pdf');
    Route::get('/print', 'print')->name('print');

    Route::get('/summaryPdf', 'summaryPdf')->name('summary-pdf');
    Route::get('/summaryPrint', 'summaryPrint')->name('summary-print');
    Route::get('/{payslip}/download', 'downloadPayslip')->name('download');

    Route::get('payslip/create', 'create')->name('create');
    // Route::get('payslip/generate/e', 'generate')->name('generate');
    Route::post('payslip/generate/e', 'generate')->name('generate');
    Route::get('payslip/{id}', 'show')->name('show');

    Route::get('tax', 'tax')->name('tax');
    Route::get('employees/provident-fund', 'emp_provident_fund')->name('emp_provident_fund');
    Route::get('provident-fund', 'provident_fund')->name('provident_fund');

    // Route::get('/payslip/send-sms', 'sendPayslipSms')->name('send_sms.job');
    Route::get('/payslip/send-sms/{month}', 'sendPayslipSms')->name('send_sms.job');
    Route::post('/payslip/update_ssbd_field', 'update_ssbd_field')->name('update_ssbd_field');
});


Route::prefix('payroll-approval')
    ->name('payroll-approval.')
    ->controller(PayrollApprovalController::class)
    ->group(function () {

        Route::post('/approve', 'approve')->name('approve');
        Route::post('/next', 'next')->name('next');
        Route::post('/return', 'return')->name('return');
        Route::post('/re-generate', 'reGenerate')->name('reGenerate');
        Route::post('/payment-confirm', 'paymentConfirm')->name('paymentConfirm');
        Route::post('/bank-file', 'bankFile')->name('bankFile');
        Route::post('/payslip/update-heads', 'updateHeads')->name('updateHeads');
    });



Route::controller(BonusController::class)->prefix('bonuses')->name('bonuses.')->group(function () {
    Route::get('/', 'bonuses')->name('index');
    Route::get('create', 'create')->name('create');
    Route::post('generate', 'generate')->name('generate');
});
Route::controller(BonusController::class)->name('bonuses.category.')
    ->prefix('bonuse-category')->group(function () {
        Route::get('/', 'categoryIndex')->name('index');
        Route::post('save', 'saveRecordcategory')->name('save');
        Route::post('update', 'updateRecordcategory')->name('update');
        Route::delete('delete', 'deleteRecordcategory')->name('delete');
    });

Route::controller(GradeController::class)->prefix('grades')->name('grades.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('create', 'create')->name('create');
    Route::post('store', 'store')->name('store');
    Route::get('edit/{id}', 'edit')->name('edit');
    Route::put('update/{id}', 'update')->name('update');
    Route::delete('delete/{id}', 'delete')->name('delete');
});


Route::get('rewards', [RewardController::class, 'rewards'])->name('rewards');
Route::get('rewards/create', [RewardController::class, 'create'])->name('rewards.create');
Route::post('rewards', [RewardController::class, 'store'])->name('rewards.store');
Route::delete('rewards/{id}', [RewardController::class, 'delete'])->name('rewards.destroy');

Route::get('auto-rewards', [AutoRewardController::class, 'index'])->name('auto-rewards.index');
Route::post('auto-rewards', [AutoRewardController::class, 'create_or_update'])->name('auto-rewards.store');

Route::get('auto-duty-bonuses/bonuses', [DutyBonusController::class, 'bonuses'])->name('auto-duty-bonuses-bonuses');
Route::resource('auto-duty-bonuses', DutyBonusController::class);

Route::resource('emp-bill-category', EmployeeBillsCategoryController::class);
Route::resource('emp-bills', EmpBillController::class);

Route::controller(PayslipPaymentController::class)
    ->prefix('paysl-payment')->name('payslip-payment.')
    ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('pay', 'pay')->name('pay');
    Route::delete('delete/{payslipPayment}', 'destroy')->name('destroy');
});

Route::controller(\App\Http\Controllers\ProvidentFundPaymentController::class)
    ->prefix('pf-payment')->name('pf-payment.')
    ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('pay', 'pay')->name('pay');
    Route::get('balance/{user_id}', 'getEmployeeBalance')->name('balance');
});

