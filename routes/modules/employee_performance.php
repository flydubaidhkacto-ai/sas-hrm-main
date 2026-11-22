<?php

use App\Http\Controllers\PerformanceCategoryController;
use App\Http\Controllers\PerformanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('performance')->name('performance.')->group(function () {
    Route::get('/categories', [PerformanceCategoryController::class, 'index'])->name('categories');
    Route::post('/categories/create', [PerformanceCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/assign/{month}', [PerformanceCategoryController::class, 'assign'])->name('categories.assign');
    Route::delete('/categories/delete/{id}', [PerformanceCategoryController::class, 'delete'])->name('categories.delete');
    Route::put('/categories/status/{id}', [PerformanceCategoryController::class, 'status'])->name('categories.status');
    Route::get('/entry', [PerformanceController::class, 'entry'])->name('entry');
    Route::get('/report', [PerformanceController::class, 'report'])->name('report');
    Route::post('/update-day', [PerformanceController::class, 'update_day'])->name('update-day');
    Route::post('/update-notes/{id}', [PerformanceController::class, 'update_notes'])->name('update-notes');
});

