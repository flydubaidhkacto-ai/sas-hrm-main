<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequestRequest;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Services\InventoryItemService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryItemRequestController extends Controller
{

    public function __construct(protected InventoryItemService $inventoryItemService) {}

    public function report(Request $request)
    {
        abort_if(!role_can('inventory-item-request') && !role_can('inventory'), 403);

        $query = InventoryItemRequest::query();

        // Date range filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->input('date_from') . ' 00:00:00',
                $request->input('date_to') . ' 23:59:59'
            ]);
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Employee filter
        if ($request->filled('employee_id') && $request->input('employee_id') !== 'all') {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // Item filter
        if ($request->filled('inventory_item_id') && $request->input('inventory_item_id') !== 'all') {
            $query->whereHas('inventoryItems', function ($q) use ($request) {
                $q->where('inventory_items.id', $request->input('inventory_item_id'));
            });
        }

        $inventory_requests = $query->with(['employee', 'inventoryItems'])->latest()->get();

        $employees = \App\Models\User::active_employees()->get();
        $items = InventoryItem::all();

        return view('inventory.item_request.report', [
            'inventory_requests' => $inventory_requests,
            'employees' => $employees,
            'items' => $items,
            'filters' => $request->all()
        ]);
    }


    public function index()
    {
        abort_if(!role_can('inventory-item-request') && role_can('inventory') && !\App\Models\ApprovalProcess::canApprove('inventory_item_request', user()), 403);
        $inventory_request = InventoryItemRequest::query()
            ->where('status', 'pending')
            ->latest();
        if (role_can('inventory')) {
            // dd(1);
        } elseif (\App\Models\ApprovalProcess::canApprove('inventory_item_request', user())) {
            $emps = \App\Models\ApprovalProcess::canApproveEmployees('inventory_item_request', user());
            $inventory_request->whereIn('employee_id', [
                ...($emps ? collect($emps['employees'])->pluck('id')->toArray() : []),
                user()->id
            ]);
        } else {
            $inventory_request->where('employee_id', user()->id);
        }
        return view('inventory.item_request.index', [
            'inventory_request' => $inventory_request->get()
        ]);
    }
    public function allItemRequest()
    {
        abort_if(!role_can('inventory-item-request') && role_can('inventory') && !\App\Models\ApprovalProcess::canApprove('inventory_item_request', user()), 403);
        $inventory_request = InventoryItemRequest::latest();
        if (role_can('inventory')) {
        } elseif (\App\Models\ApprovalProcess::canApprove('inventory_item_request', user())) {
            $emps = \App\Models\ApprovalProcess::canApproveEmployees('inventory_item_request', user());
            $inventory_request->whereIn('employee_id', [
                ...($emps ? collect($emps['employees'])->pluck('id')->toArray() : []),
                user()->id
            ]);
        } else {
            $inventory_request->where('employee_id', user()->id);
        }
        return view('inventory.item_request.all_request', [
            'inventory_request' => $inventory_request->get()
        ]);
    }

    public function create()
    {
        abort_if(!role_can('inventory-item-request') && !role_can('inventory'), 403);
        $item_list = InventoryItem::latest()
            ->get();
        // ->filter(function ($item) {
        //     return $item->current_quantity > 0;
        // });
        return view("inventory.item_request.create", compact("item_list"));
    }

    public function store(StoreInventoryItemRequestRequest $request)
    {
        abort_if(!role_can('inventory-item-request') && !role_can('inventory'), 403);
        $formData = $request->validated();

        DB::beginTransaction();
        try {
            // Prepare the main request data
            $requestFormData = [
                'employee_id' => $formData['employee_id'],
                'description' => $formData['description'] ?? null
            ];

            if (user()->is_super_admin()) {
                $requestFormData['status'] = 'approved';
            } else {
                $requestFormData['status'] = 'pending';
            }

            $inventoryItemRequest = $this->inventoryItemService->store($requestFormData, $formData['inventory_items']);

            if (user()->is_super_admin()) {
                $inventoryItemRequest->approvedAll();
            }

            DB::commit();
            Toastr::success('Inventory Item Request created successfully.');
            return redirect()->route('inventory_item_request');
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    public function itemRequestEditShow($id)
    {
        $inventory_request = InventoryItemRequest::findOrFail($id);
        return view('inventory.item_request.show', compact('inventory_request'));
    }

    public function delete($id, $requestPage)
    {
        abort_if(!role_can('inventory'), 403);
        $inventory_request = InventoryItemRequest::findOrFail($id);
        $inventory_request->delete();
        Toastr::success('Inventory Item Request deleted successfully.');
        // echo($requestPage);
        if ($requestPage == 'list') {
            return redirect()->route('inventory_item_request');
        } elseif ($requestPage == 'listAll') {
            return redirect()->route('inventory_item_request.all');
        }
    }

    // public function itemRequestApproved($id)
    // {
    //     abort_if(!role_can('inventory-item-request') && !role_can('inventory'), 403);
    //     $inventory_request = InventoryItemRequest::findOrFail($id);
    //     $inventory_request->status = 'approved';
    //     $inventory_request->approved_at = now();
    //     $inventory_request->processed_by = user()->id;
    //     $inventory_request->save();
    //     Toastr::success('Inventory Item Request approved successfully.');
    //     return redirect()->route('inventory_item_request.all');
    // }

    // public function itemRequestRejected($id)
    // {
    //     abort_if(!role_can('inventory-item-request'), 403);
    //     $inventory_request = InventoryItemRequest::findOrFail($id);
    //     $inventory_request->status = 'rejected';
    //     $inventory_request->rejected_at = now();
    //     $inventory_request->processed_by = user()->id;
    //     $inventory_request->save();
    //     Toastr::success('Inventory Item Request rejected successfully.');
    //     return redirect()->route('inventory_item_request.all');
    // }

    public function itemReceivedOrNot(Request $request, $id)
    {
        abort_if(!role_can('inventory-item-request') && !role_can('inventory'), 403);
        $status = $request->input('status');
        $received_note = $request->input('received_notes');
        // echo($status);
        $inventory_request = InventoryItemRequest::findOrFail($id);
        if ($status == 'received') {
            $inventory_request->received_status = 'received';
            $inventory_request->received_at = now();
            $inventory_request->received_notes = $received_note;
            $inventory_request->save();
            Toastr::success('Inventory item request received successfully');
            return redirect()->route('inventory_item_request.all');
        } elseif ($status == 'not_received') {
            $inventory_request->received_status = 'not_received';
            $inventory_request->received_at = now();
            $inventory_request->received_notes = $received_note;
            $inventory_request->save();
            Toastr::success('Inventory item request not received successfully');
            return redirect()->route('inventory_item_request.all');
        }
    }
}
