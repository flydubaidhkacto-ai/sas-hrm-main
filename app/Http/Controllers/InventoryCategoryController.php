<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Http\Requests\StoreInventoryCategoryRequest;
use App\Http\Requests\UpdateInventoryCategoryRequest;

class InventoryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!role_can('inventory'), 403);
        $categories = InventoryCategory::simplePaginate(15);
        return view('inventory.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryCategoryRequest $request)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        InventoryCategory::create($formData);

        return redirect()->route('inventory.category.index')->with('success', 'Inventory Category Saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryCategory $inventoryCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryCategory $category)
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryCategoryRequest $request, InventoryCategory $category)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();

        $category->update($formData);

        return redirect()->route('inventory.category.index')->with('success', 'Inventory Category Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryCategory $category)
    {
        abort_if(!role_can('inventory'), 403);
        $category->delete();

        return redirect()->back()->with('error', 'Inventory Category Deleted successfully');
    }
}
