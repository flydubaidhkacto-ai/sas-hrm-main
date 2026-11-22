<?php
use App\Http\Controllers\ZktecoCloudController;
use Illuminate\Support\Facades\Route;

Route::get('zkteco-cloud', [ZktecoCloudController::class, 'index'])
    ->name('zkteco-cloud');
Route::get('zkteco-cloud/create', [ZktecoCloudController::class, 'create'])
    ->name('zkteco-cloud.create');
Route::post('zkteco-cloud/store', [ZktecoCloudController::class, 'store'])
    ->name('zkteco-cloud.store');
Route::get('zkteco-cloud/device/{id}', [ZktecoCloudController::class, 'device'])
    ->name('zkteco-cloud.device');
Route::get('zkteco-cloud/device/{id}/sync/{type}', [ZktecoCloudController::class, 'sync'])
    ->name('zkteco-cloud.device.sync');
Route::get('zkteco-cloud/device/delete-command/{id}', [ZktecoCloudController::class, 'delete_command'])
    ->name('zkteco-cloud.device.delete-command');
Route::get('zkteco-cloud/edit/{id}', [ZktecoCloudController::class, 'edit'])
    ->name('zkteco-cloud.edit');
Route::put('zkteco-cloud/update/{id}', [ZktecoCloudController::class, 'update'])
    ->name('zkteco-cloud.update');
Route::get('zkteco-cloud/delete/{id}', [ZktecoCloudController::class, 'delete'])
    ->name('zkteco-cloud.delete');
Route::post('zkteco-cloud/device/assign-departments/{device}', [ZktecoCloudController::class, 'assign_department'])
    ->name('zkteco-cloud.device.assign-departments');
    
