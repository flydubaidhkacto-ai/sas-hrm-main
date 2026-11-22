<?php

namespace App\Http\Controllers;

use App\Models\InventorySupplier;
use App\Http\Requests\StoreInventorySupplierRequest;
use App\Http\Requests\UpdateInventorySupplierRequest;

class InventorySupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!role_can('inventory'), 403);
        $suppliers = InventorySupplier::simplePaginate(15);
        return view('inventory.supplier.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.supplier.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventorySupplierRequest $request)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        InventorySupplier::create($formData);

        return redirect()->route('inventory.supplier.index')->with('success', 'Inventory Supplier Saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventorySupplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventorySupplier $supplier)
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.supplier.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventorySupplierRequest $request, InventorySupplier $supplier)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        $supplier->update($formData);

        return redirect()->route('inventory.supplier.index')->with('success', 'Inventory Supplier Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventorySupplier $supplier)
    {
        abort_if(!role_can('inventory'), 403);
        $supplier->delete();

        return redirect()->back()->with('error', 'Inventory Supplier Deleted successfully');
    }
}
