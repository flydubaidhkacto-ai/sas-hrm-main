<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryItemRequestResource;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Models\User;
use App\Services\InventoryItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryItemController extends Controller
{

    public function __construct(protected InventoryItemService $inventoryItemService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['apply_date', 'status']);
        // dd($filters);
        $inventoryItemRequests = InventoryItemRequest::filter($filters)->get();

        return ApiResponseClass::sendResponse(InventoryItemRequestResource::collection($inventoryItemRequests), 'Inventory Item Request fetched successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // abort_if(!role_can('inventory-item-request'), 403);
        $formData = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'description' => 'nullable|string'
        ]);

        $user = $request->auth_user;

        DB::beginTransaction();
        try {
            if ($user->is_super_admin() || role_can('inventory-item-request')) {
                $formData['status'] = 'approved';
            } else {

                $formData['status'] = 'pending';
            }
            $inventoryItemRequest = $this->inventoryItemService->store($formData, $request->auth_user);

            if ($user->is_super_admin() || role_can('inventory-item-request')) {
                $inventoryItemRequest->approvedAll();
            }

            DB::commit();
            return ApiResponseClass::sendResponse(new InventoryItemRequestResource($inventoryItemRequest), 'Inventory Item Request successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponseClass::throw($e, 'An error occurred while processing inventory item request.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        $user = $request->auth_user;
        $data = InventoryItemRequest::with('inventoryItem', 'employee.department.super_department', 'employee.designation')->findOrFail($id);
        $approvalSteps = $data->getApprovalStepsApi($user->id) ?: [];
        return ApiResponseClass::sendResponse([
            'data' => $data,
            'approvalSteps' => $approvalSteps
        ], 'Inventory Item Request fetched successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function selectInventory()
    {
        // return InventoryItem::pluck('name', 'id');
        return InventoryItem::get(['name', 'id']);
    }
}
