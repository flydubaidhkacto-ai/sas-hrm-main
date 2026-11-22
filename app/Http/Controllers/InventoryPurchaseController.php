<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryPurchaseDataTable;
use App\Models\InventoryPurchase;
use App\Http\Requests\StoreInventoryPurchaseRequest;
use App\Http\Requests\UpdateInventoryPurchaseRequest;
use App\Models\InventoryItem;
use App\Models\InventorySupplier;
use Illuminate\Http\Request;

class InventoryPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index (InventoryPurchaseDataTable $dataTable)
    {
        abort_if(!role_can('inventory'), 403);
        $suppliers = InventorySupplier::where('status', 1)->get();
        return $dataTable->render('inventory.purchase.list', compact('suppliers'));
        // $purchases = InventoryPurchase::simplePaginate(15);
        // return view('inventory.purchase.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!role_can('inventory'), 403);
        $suppliers = InventorySupplier::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        $items = InventoryItem::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        return view('inventory.purchase.create', compact('suppliers', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryPurchaseRequest $request)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        // dd($formData);
        $inventory_purchase = InventoryPurchase::create($formData);

        // Attach items with quantities
        $itemsWithQuantities = [];

        foreach ($formData['inventory_items'] as $key => $itemID) {
            $itemsWithQuantities[$itemID] = ['quantity' => $formData['quantity'][$key]];
        }

        $inventory_purchase->rel_to_inv_item()->attach($itemsWithQuantities);

        return redirect()->route('inventory.purchase.index')->with('success', 'Inventory Purchase created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryPurchase $purchase)
    {
        abort_if(!role_can('inventory'), 403);
        $purchase->load('rel_to_inv_item');
        return view('inventory.purchase.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryPurchase $purchase)
    {
        abort_if(!role_can('inventory'), 403);
        $purchase->load('rel_to_inv_item');
        // dd($purchase);

        $suppliers = InventorySupplier::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        $items = InventoryItem::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        return view('inventory.purchase.edit', compact('purchase', 'suppliers', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryPurchaseRequest $request, InventoryPurchase $purchase)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        $purchase->update($formData);

        // Detach previous items and attach new ones
        $purchase->rel_to_inv_item()->detach();
        $itemsWithQuantities = [];

        foreach ($formData['inventory_items'] as $key => $itemID) {
            $itemsWithQuantities[$itemID] = ['quantity' => $formData['quantity'][$key]];
        }

        $purchase->rel_to_inv_item()->attach($itemsWithQuantities);

        return redirect()->route('inventory.purchase.index')->with('success', 'Inventory Purchase updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryPurchase $purchase)
    {
        abort_if(!role_can('inventory'), 403);
        $purchase->delete();

        return redirect()->back()->with('error', 'Inventory Purchase Deleted successfully');
    }
}
