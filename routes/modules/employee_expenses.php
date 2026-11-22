<?php

use App\Http\Controllers\EmpDailyExpenseCategoryController;
use App\Http\Controllers\EmpDailyExpenseController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'emp-expense',
    'as' => 'emp-expenses.',
], function () {
    // Resource controller for categories
    Route::resource('category', EmpDailyExpenseCategoryController::class);

});

Route::group([
    'prefix' => 'emp-expenses',
    'as' => 'emp-expenses.',
], function () {
    // Define explicit routes with controller assigned
    Route::controller(EmpDailyExpenseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
    });
});
