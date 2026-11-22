<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'code', 'alert_quantity', 'image', 'inventory_category_id', 'inventory_unit_id', 'total_cost'];

    public function rel_to_inv_category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function rel_to_inv_unit()
    {
        return $this->belongsTo(InventoryUnit::class, 'inventory_unit_id');
    }
    public function rel_to_inv_purchases()
    {
        return $this->belongsToMany(InventoryPurchase::class, 'inventory_purchase_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function inventoryItemRequests()
    {
        return $this->belongsToMany(InventoryItemRequest::class, 'inventory_item_request_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getCurrentQuantityAttribute()
    {
        // Total purchased quantity
        $totalPurchased = $this->rel_to_inv_purchases()->sum('quantity');

        // Total requested quantity for approved requests via pivot
        $totalRequested = $this->inventoryItemRequests()
            ->where('inventory_item_requests.status', 'approved')
            ->sum('inventory_item_request_items.quantity');

        return $totalPurchased - $totalRequested;
    }
}
