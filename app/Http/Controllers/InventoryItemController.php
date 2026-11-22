<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Models\InventoryItemRequest;
use App\Models\UniqueBarcode;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!role_can('inventory'), 403);
        $items = InventoryItem::get();
        return view('inventory.item.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!role_can('inventory'), 403);
        if(InventoryUnit::count() == 0){
            InventoryUnit::create([
                'name' => 'Piece',
                'status' => 1,
            ]);
            InventoryCategory::create([
                'name' => 'Default',
                'status' => 1,
            ]);
        }
        $categories = InventoryCategory::where('status', 1)->get();
        $units = InventoryUnit::where('status', 1)->get();
        return view('inventory.item.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryItemRequest $request)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();
        // Generate the code
        $formData['code'] = "SAS" . (str_pad(1 + (InventoryItem::latest()->first()->id ?? 0), 3, '0', STR_PAD_LEFT)) . "/" . date("y");

        InventoryItem::create($formData);

        return redirect()->route('inventory.item.index')->with('success', 'Inventory Item Saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryItem $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $item)
    {
        abort_if(!role_can('inventory'), 403);
        $categories = InventoryCategory::where('status', 1)->get();
        $units = InventoryUnit::where('status', 1)->get();
        return view('inventory.item.edit', compact('item', 'categories', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $item)
    {
        abort_if(!role_can('inventory'), 403);
        $formData = $request->validated();

        $item->update($formData);

        return redirect()->route('inventory.item.index')->with('success', 'Inventory Item Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $item)
    {
        abort_if(!role_can('inventory'), 403);
        $item->delete();

        return redirect()->back()->with('error', 'Inventory Item Deleted successfully');
    }

    public function labelPrint(Request $request)
    {
        abort_if(!role_can('inventory'), 403);
        if($request->label_print && $request->count) {
            // generate uniqe codes for barcode
            $codes = [];
            for ($i=1; $i <= $request->count; $i++) {
                $codes[] = UniqueBarcode::create([
                    'code' => substr(bin2hex(random_bytes(5)), 0, 10),
                    'model_id' => $request->label_print,
                    'type' => 'asset'
                ]);
            }
            return $codes;
        }

        $items = InventoryItem::all();
        return view('inventory.item.label-print', compact('items'));
    }

    public function barcodeScanner()
    {
        abort_if(!role_can('inventory'), 403);
        return view('inventory.item.barcode-scanner');
    }

    public function barcodeScan(Request $request)
    {
        abort_if(!role_can('inventory'), 403);

        $request->validate([
            'barcode' => 'required|string'
        ]);

        // Find inventory item by code (barcode)
        $item = InventoryItem::where('code', $request->barcode)
                            ->with(['rel_to_inv_category', 'rel_to_inv_unit'])
                            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found with this barcode'
            ], 404);
        }

        // Get all requests for this item with request details
        $itemRequests = InventoryItemRequest::whereHas('items', function($query) use ($item) {
                                                $query->where('inventory_item_id', $item->id);
                                            })
                                            ->with(['employee', 'items' => function($query) use ($item) {
                                                $query->where('inventory_item_id', $item->id)->with('item');
                                            }])
                                            ->orderBy('created_at', 'desc')
                                            ->get();

        return response()->json([
            'success' => true,
            'item' => $item,
            'requests' => $itemRequests
        ]);
    }
}
