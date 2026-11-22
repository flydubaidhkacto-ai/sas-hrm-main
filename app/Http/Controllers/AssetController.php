<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asset;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\SuperDepartment;
use App\Models\UniqueBarcode;
use App\Exports\AssetList;
use Brian2694\Toastr\Facades\Toastr;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(!role_can('asset-manage'), 403);

        $query = Asset::latest()
            ->with('asset_category', 'super_department');

        if ($request->filled('tag')) {
            $query->where('tag', 'like', '%' . $request->tag . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('user')) {
            $query->where('user', 'like', '%' . $request->user . '%');
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('tag', 'like', '%' . $search . '%')
                    ->orWhere('user', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhereHas('asset_category', function ($sq) use ($search) {
                        $sq->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('super_department', function ($sq) use ($search) {
                        $sq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $data = $query->get();

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return AssetList::download_excel($data, 'Asset List.xlsx');
            }
            if ($as == 'pdf') {
                return AssetList::download_pdf($data, 'Asset List.pdf');
            }
            if ($as == 'print') {
                return AssetList::print($data);
            }
        }

        return view('asset.index', [
            'data' => $data,
            'categories' => AssetCategory::all(),
        ])->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('asset.create', [
            'category' => AssetCategory::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!role_can('asset-manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tag' => 'required|string|unique:assets,tag',
            'category_id' => 'required|string',
            'status' => 'required|string',
            'client' => 'nullable|string',
            'user' => 'nullable|string',
            'location_id' => 'nullable|integer',
            'supplier' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'model' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiration' => 'nullable|date',
            'serial_number' => 'nullable|string|max:255',
            'present_value' => 'nullable|numeric',
            'purchase_value' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $data['purchase_date'] = date('Y-m-d', strtotime($request->purchase_date));
        $data['warranty_expiration'] = date('Y-m-d', strtotime($request->warranty_expiration));

        if ($request->filled('location_id')) {
            $superDepartment = SuperDepartment::find($request->location_id);
            $data['location_id'] = $request->location_id;
            $data['location'] = $superDepartment ? $superDepartment->name : null;
        } else {
            $data['location_id'] = null;
            $data['location'] = null;
        }

        Asset::create($data);

        Toastr::success('Asset added successfully!');
        return redirect()->route('assets-management.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $assets_management)
    {
        return view('asset.show', [
            'asset' => $assets_management
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $assets_management)
    {
        abort_if(!role_can('asset-manage'), 403);

        return view('asset.edit', [
            'asset' => $assets_management,
            'category' => AssetCategory::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $assets_management)
    {
        abort_if(!role_can('asset-manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tag' => 'required|string|unique:assets,tag,' . $assets_management->id,
            'category_id' => 'required|string',
            'status' => 'required|string',
            'client' => 'nullable|string',
            'user' => 'nullable|string',
            'location_id' => 'nullable|integer',
            'supplier' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'model' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiration' => 'nullable|date',
            'serial_number' => 'nullable|string|max:255',
            'present_value' => 'nullable|numeric',
            'purchase_value' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $data['purchase_date'] = date('Y-m-d', strtotime($request->purchase_date));
        $data['warranty_expiration'] = date('Y-m-d', strtotime($request->warranty_expiration));

        if ($request->filled('location_id')) {
            $superDepartment = SuperDepartment::find($request->location_id);
            $data['location_id'] = $request->location_id;
            $data['location'] = $superDepartment ? $superDepartment->name : null;
        } else {
            $data['location_id'] = null;
            $data['location'] = null;
        }

        $assets_management->update($data);
        Toastr::success('Asset updated successfully!');
        return redirect()->route('assets-management.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $assets_management)
    {
        abort_if(!role_can('asset-manage'), 403);
        $assets_management->delete();
        Toastr::success('Asset deleted successfully!');
        return redirect()->route('assets-management.index');
    }

    public function labelPrint(Request $request)
    {
        abort_if(!role_can('inventory'), 403);
        return view('asset.label-print', [
            'assets' => Asset::whereIn('id', $request->ids ? explode(',', $request->ids) : [])->get()
        ]);
    }

    public function barcodeScanner()
    {
        abort_if(!role_can('asset-manage'), 403);
        return view('asset.barcode-scanner');
    }

    public function barcodeScan(Request $request)
    {
        abort_if(!role_can('asset-manage'), 403);

        $request->validate([
            'barcode' => 'required|string'
        ]);

        $asset = Asset::where('tag', $request->barcode)
                     ->with('asset_category')
                     ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found with this barcode'
            ], 404);
        }

        $assetRequests = AssetRequest::where('asset_id', $asset->id)
                                   ->with('employee')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json([
            'success' => true,
            'asset' => $asset,
            'requests' => $assetRequests
        ]);
    }
}
