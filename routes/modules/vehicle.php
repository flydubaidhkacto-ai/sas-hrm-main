<?php
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleServiceController;
use App\Http\Controllers\VehicleRefuelingController;
use Illuminate\Support\Facades\Route;

//--------------------------------------Vehicle Management--------------------------------//
Route::get('vehicle', [VehicleController::class, 'index'])->name('vehicle');
Route::get('vehicle/create', [VehicleController::class, 'create'])->name('vehicle.create');
Route::post('vehicle/store', [VehicleController::class, 'store'])->name('vehicle.store');
Route::get('vehicle/{id}', [VehicleController::class, 'edit'])->name('vehicle.edit');
Route::put('vehicle/update', [VehicleController::class, 'update'])->name('vehicle.update');
Route::delete('vehicle/delete/{id}', [VehicleController::class, 'delete'])->name('vehicle.delete');

//--------------------------------------Vehicle Service Management--------------------------------//
Route::get('vehicle-service', [VehicleServiceController::class, 'index'])->name('vehicle_service');
Route::get('vehicle-service/create', [VehicleServiceController::class, 'create'])->name('vehicle_service.create');
Route::post('vehicle-service/store', [VehicleServiceController::class, 'store'])->name('vehicle_service.store');
Route::get('vehicle-service/edit/{id}', [VehicleServiceController::class, 'edit'])->name('vehicle_service.edit');
Route::put('vehicle-service/update', [VehicleServiceController::class, 'update'])->name('vehicle_service.update');
Route::delete('vehicle-service/delete/{id}', [VehicleServiceController::class, 'delete'])->name('vehicle_service.delete');

Route::resource('refuelings', VehicleRefuelingController::class);
