<?php
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetRequestController;
use Illuminate\Support\Facades\Route;

//--------------------------------------asset category--------------------------------//
Route::controller(AssetCategoryController::class)->prefix('asset-category')->name('asset_category.')->group( function () {
    Route::get('/', 'index')->name('index');
    Route::post('store', 'store')->name('store');
    Route::post('update', 'update')->name('update');
    Route::delete('grade-system/{id}', 'delete')->name('delete');
});

//--------------------------------------asset manage--------------------------------//
Route::resource('assets-management', AssetController::class);
Route::post('asset-label-print', [AssetController::class, 'labelPrint'])->name('asset_label_print');
Route::get('asset-barcode-scanner', [AssetController::class, 'barcodeScanner'])->name('asset_barcode_scanner');
Route::post('asset-barcode-scan', [AssetController::class, 'barcodeScan'])->name('asset_barcode_scan');

//--------------------------------------asset Request--------------------------------//
Route::get('asset-request/all', [AssetRequestController::class, 'index'])->name('asset_request');
Route::get('asset-request/create', [AssetRequestController::class, 'create'])->name('asset_request.create');
Route::post('asset-request/store', [AssetRequestController::class, 'store'])->name('asset_request.store');
Route::put('asset-request', [AssetRequestController::class, 'update'])->name('asset_request.update');
Route::delete('asset-request/{id}', [AssetRequestController::class, 'delete'])->name('asset_request.delete');

Route::get('asset-req-all', [AssetRequestController::class, 'allRequest'])->name('asset_request.all');
Route::get('asset-request/report', [AssetRequestController::class, 'report'])->name('asset_request.report');
Route::get('asset-req/edit/{id}', [AssetRequestController::class, 'assetRequestEditShow']);
// Route::get('asset-req/rejected/{id}', [AssetRequestController::class, 'assetRequestRejected'])->name('asset_request.rejected');
// Route::get('asset-req/approved/{id}', [AssetRequestController::class, 'assetRequestApproved'])->name('asset_request.approved');
