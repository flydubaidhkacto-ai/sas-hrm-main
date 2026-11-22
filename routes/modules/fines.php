<?php

use App\Http\Controllers\AbsentFineRuleController;
use App\Http\Controllers\AutoAbsentFineController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\HalfDayFineController;
use Illuminate\Support\Facades\Route;

Route::controller(FineController::class)->prefix('fines')->name('fines.')->group( function () {
    Route::delete('destroy/{id}', 'destroy')->name('destroy');
    Route::get('/', 'fines')->name('index');
    Route::post('apply', 'apply')->name('apply');
    Route::post('decide/{id}', 'decide')->name('decide');
    Route::get('create', 'create')->name('create');
});
Route::controller(FineController::class)
    ->prefix('auto-late-fine')->name('fines.auto-late-fine.')->group( function () {
    Route::get('/', 'auto_late_fine')->name('index');
    Route::post('/store', 'auto_late_fine_store')->name('store');
    Route::delete('/{id}', 'auto_late_fine_delete')->name('delete');
});
Route::controller(AutoAbsentFineController::class)
    ->prefix('auto-absent-fine')->name('fines.auto-absent-fine.')->group( function () {
    Route::get('/', 'auto_absent_fine')->name('index');
    Route::post('/store', 'auto_absent_fine_store')->name('store');
    Route::delete('/{id}', 'auto_absent_fine_delete')->name('delete');
});
Route::resource('fines-absent', AbsentFineRuleController::class);
Route::resource('half-day-fines', HalfDayFineController::class);
