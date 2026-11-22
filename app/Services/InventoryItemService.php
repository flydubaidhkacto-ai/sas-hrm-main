<?php

namespace App\Services;

use App\Models\InventoryItemRequest;
use Illuminate\Http\RedirectResponse;

class InventoryItemService
{

    public function allItemRequest()
    {
        return $inventory_request = InventoryItemRequest::latest();
    }

    public function store($formData, $items = []): InventoryItemRequest
    {
        $formData['received_status'] = false;

        $inventoryItemRequest = InventoryItemRequest::create($formData);

        foreach ($items as $item) {
            $inventoryItemRequest->items()->create([
                'inventory_item_id' => $item['id'],
                'quantity' => $item['quantity']
            ]);
        }

        return $inventoryItemRequest;
    }
}
