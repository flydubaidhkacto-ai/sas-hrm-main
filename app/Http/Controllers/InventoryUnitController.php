<?php

namespace App\Http\Controllers;

use App\Models\InventoryUnit;
use App\Http\Requests\StoreInventoryUnitRequest;
use App\Http\Requests\UpdateInventoryUnitRequest;

class InventoryUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!role_can('inventory'), 403);
        $units = InventoryUnit::simplePaginate(15);
        return view('inventory.unit.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryUnitRequest $request)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        InventoryUnit::create($formData);

        return redirect()->route('inventory.unit.index')->with('success', 'Inventory Category Saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryUnit $unit)
    {
        abort_if(!role_can('inventory'), 403);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryUnit $unit)
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.unit.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryUnitRequest $request, InventoryUnit $unit)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();

        $unit->update($formData);

        return redirect()->route('inventory.unit.index')->with('success', 'Inventory Unit Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryUnit $unit)
    {
        abort_if(!role_can('inventory'), 403);
        $unit->delete();

        return redirect()->back()->with('error', 'Inventory Unit Deleted successfully');
    }
}
