<?php
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\AccountPayableController;
use App\Http\Controllers\AccountsReceivableController;
use Illuminate\Support\Facades\Route;

Route::resource('chart-of-accounts', ChartOfAccountController::class);
Route::resource('transactions', TransactionController::class);
Route::resource('transfers', AccountTransferController::class);
Route::resource('bills', BillController::class);
Route::resource('vendors', VendorController::class);
Route::resource('budgets', BudgetController::class);


Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('profit.loss');
Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance.sheet');
Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash.flow');

Route::get('accounts/payable', [AccountPayableController::class, 'index'])->name('accounts.payable');
Route::get('/accounts/receivable', [AccountsReceivableController::class, 'index'])->name('accounts.receivable');
Route::get('/account/make/received/{id}', [AccountsReceivableController::class, 'make_received'])->name('account.make.received');
