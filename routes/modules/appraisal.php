<?php

use App\Http\Controllers\AppraisalController;
use Illuminate\Support\Facades\Route;

// Loaded inside the authenticated group in routes/web.php ($modules list).
Route::get('/appraisal-setup', [AppraisalController::class, 'setup'])->name('appraisals.setup');
Route::post('/appraisal-setup', [AppraisalController::class, 'setupSave'])->name('appraisals.setup.save');

Route::get('/appraisals', [AppraisalController::class, 'index'])->name('appraisals.index');
Route::get('/appraisals/create', [AppraisalController::class, 'create'])->name('appraisals.create');
Route::post('/appraisals', [AppraisalController::class, 'store'])->name('appraisals.store');
Route::get('/appraisals/{appraisal}', [AppraisalController::class, 'show'])->name('appraisals.show');
Route::get('/appraisals/{appraisal}/print', [AppraisalController::class, 'print'])->name('appraisals.print');
Route::put('/appraisals/{appraisal}/supervisor', [AppraisalController::class, 'saveSupervisor'])->name('appraisals.supervisor');
Route::put('/appraisals/{appraisal}/employee', [AppraisalController::class, 'saveEmployee'])->name('appraisals.employee');
Route::post('/appraisals/{appraisal}/review/{stage}', [AppraisalController::class, 'review'])->name('appraisals.review');
