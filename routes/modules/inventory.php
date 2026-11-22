<?php
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryItemRequestController;
use App\Http\Controllers\InventoryPurchaseController;
use App\Http\Controllers\InventorySupplierController;
use App\Http\Controllers\InventoryUnitController;
use Illuminate\Support\Facades\Route;


// inventory routes
Route::group([
    'prefix' => 'inventory',
    'as' => 'inventory.'
], function () {
    Route::resource('category', InventoryCategoryController::class);
    Route::resource('unit', InventoryUnitController::class);
    Route::resource('supplier', InventorySupplierController::class);
    Route::resource('item', InventoryItemController::class);
    Route::resource('purchase', InventoryPurchaseController::class);
});

Route::get('inventory-label-print', [InventoryItemController::class, 'labelPrint'])->name('inventory_label_print');
Route::get('inventory-barcode-scanner', [InventoryItemController::class, 'barcodeScanner'])->name('inventory_barcode_scanner');
Route::post('inventory-barcode-scan', [InventoryItemController::class, 'barcodeScan'])->name('inventory_barcode_scan');

//--------------------------------------item Request--------------------------------//
Route::get('inventory-item-request/all', [InventoryItemRequestController::class, 'index'])->name('inventory_item_request');
Route::get('inventory-item-request/create', [InventoryItemRequestController::class, 'create'])->name('inventory_item_request.create');
Route::post('inventory-item-request/store', [InventoryItemRequestController::class, 'store'])->name('inventory_item_request.store');
// Route::put('inventory-item-request', [InventoryItemRequestController::class, 'update'])->name('inventory_item_request.update');
Route::delete('inventory-item-request/delete/{id}/{requestPage}', [InventoryItemRequestController::class, 'delete'])->name('inventory_item_request.delete');

Route::get('inventory-item-req-all', [InventoryItemRequestController::class, 'allItemRequest'])->name('inventory_item_request.all');
Route::get('inventory-item-req/edit/{id}', [InventoryItemRequestController::class, 'itemRequestEditShow']);
Route::get('inventory-item-req/rejected/{id}', [InventoryItemRequestController::class, 'itemRequestRejected'])->name('inventory_item_request.rejected');
Route::get('inventory-item-req/approved/{id}', [InventoryItemRequestController::class, 'itemRequestApproved'])->name('inventory_item_request.approved');

Route::post('inventory-item/received-or-not/{id}', [InventoryItemRequestController::class, 'itemReceivedOrNot'])->name('inventory_item_request.receivedOrNot');


Route::get('inventory-item-request/report', [InventoryItemRequestController::class, 'report'])->name('inventory_item_request.report');

