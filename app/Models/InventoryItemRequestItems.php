<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItemRequestItems extends Model
{
    protected $fillable = ['inventory_item_request_id', 'inventory_item_id', 'quantity'];

    public function request()
    {
        return $this->belongsTo(InventoryItemRequest::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
}
