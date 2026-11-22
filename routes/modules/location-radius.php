<?php
use Illuminate\Support\Facades\Route;


Route::get('allowed-regions/create', [\App\Http\Controllers\AllowedPunchRegionController::class, 'create'])
    ->name('allowed-regions.create');
Route::post('allowed-regions', [\App\Http\Controllers\AllowedPunchRegionController::class, 'store'])
    ->name('allowed-regions.store');
Route::get('allowed-regions/delete/{id}', [\App\Http\Controllers\AllowedPunchRegionController::class, 'delete'])
    ->name('allowed-regions.delete');



