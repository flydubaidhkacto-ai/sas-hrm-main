<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'inventory_item_id' => $this->inventory_item_id,
            'description' => $this->description,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
