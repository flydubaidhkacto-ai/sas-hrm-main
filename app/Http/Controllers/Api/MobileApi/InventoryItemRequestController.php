<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Services\InventoryItemService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class InventoryItemRequestController extends Controller
{

    public function __construct(protected InventoryItemService $inventoryItemService) {}


    public function index(Request $request)
    {
        $user = $request->auth_user;
        $inventory_request = InventoryItemRequest::query();

        if(role_can('inventory'))
        {

        }
        elseif(\App\Models\ApprovalProcess::canApprove('inventory_item_request', $user))
        {
            $emps = \App\Models\ApprovalProcess::canApproveEmployees('inventory_item_request', $user);
            $inventory_request->whereIn('employee_id', [
                ...($emps ? collect($emps['employees'])->pluck('id')->toArray() : []),
                $user->id
            ]);
        }
        else
        {
            $inventory_request->where('employee_id', $user->id);
        }

        $inventory_request = $inventory_request->when(in_array($request->filter, ['pending', 'approved', 'rejected']), function ($query) use ($request) {
                $query->where('status', $request->filter);
            })
            // ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            //     $query->whereBetween('created_at', [
            //         $request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'
            //     ]);
            // })
            ->when($request->employee, function ($query) use ($request) {
                $query->where('employee_id', $request->employee);
            })
            ->with([
                'employee.designation', 'inventoryItems'
            ])
            ->latest();

        return ApiResponseClass::sendResponse($inventory_request->get(), 'Inventory Item Request fetched successfully', 200);
    }
    public function create()
    {
        $item_list = InventoryItem::latest()
            ->get();
            // ->filter(function ($item) {
            //     return $item->current_quantity > 0;
            // });
        return ApiResponseClass::sendResponse($item_list, 'Inventory Item List fetched successfully', 200);
    }

    public function store(Request $request)
    {
        abort_if(!role_can('inventory-item-request', $request->auth_user), 403);
        $formData = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'inventory_items' => 'required|array|min:1',
            'inventory_items.*.id' => 'required|exists:inventory_items,id',
            'inventory_items.*.quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $user = $request->auth_user;
        DB::beginTransaction();
        try {
            $requestFormData = [
                'employee_id' => $formData['employee_id'],
                'description' => $formData['description'] ?? null
            ];
            if ($user->is_super_admin()) {
                $requestFormData['status'] = 'approved';
            } else {
                $requestFormData['status'] = 'pending';
            }
            $inventoryItemRequest = $this->inventoryItemService->store($requestFormData, $formData['inventory_items']);
            if ($user->is_super_admin()) {
                $inventoryItemRequest->approvedAll();
            }
            DB::commit();
            return ApiResponseClass::sendResponse($inventoryItemRequest, 'Inventory Item Request created successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return abort(422, $e->getMessage());
        }
    }

    public function show($id, Request $request)
    {
        $inventory_request = InventoryItemRequest::findOrFail($id);
        $inventory_request->load([
            'employee.designation', 'inventoryItems'
        ]);
        $data = [
            'approvalSteps' => $inventory_request->getApprovalStepsApi($request->auth_user->id),
            ...($inventory_request->toArray()),
        ];
        return ApiResponseClass::sendResponse($data, 'Inventory Item Request fetched successfully', 200);
    }

    public function delete($id, $requestPage)
    {
        abort_if(!role_can('inventory-item-request'), 403);
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

    public function itemRequestApproved($id)
    {
        abort_if(!role_can('inventory-item-request'), 403);
        $inventory_request = InventoryItemRequest::findOrFail($id);
        $inventory_request->status = 'approved';
        $inventory_request->approved_at = now();
        $inventory_request->processed_by = user()->id;
        $inventory_request->save();
        Toastr::success('Inventory Item Request approved successfully.');
        return redirect()->route('inventory_item_request.all');
    }

    public function itemRequestRejected($id)
    {
        abort_if(!role_can('inventory-item-request'), 403);
        $inventory_request = InventoryItemRequest::findOrFail($id);
        $inventory_request->status = 'rejected';
        $inventory_request->rejected_at = now();
        $inventory_request->processed_by = user()->id;
        $inventory_request->save();
        Toastr::success('Inventory Item Request rejected successfully.');
        return redirect()->route('inventory_item_request.all');
    }

    public function itemReceivedOrNot(Request $request, $id)
    {
        abort_if(!role_can('inventory-item-request'), 403);
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
