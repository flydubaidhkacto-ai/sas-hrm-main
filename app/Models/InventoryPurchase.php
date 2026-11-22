<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchase extends Model
{
    protected $fillable = ['inventory_supplier_id', 'challan_number', 'ref_no', 'total_cost'];

    public function rel_to_inv_item()
    {
        return $this->belongsToMany(InventoryItem::class, 'inventory_purchase_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function rel_to_inv_supplier() {
        return $this->belongsTo(InventorySupplier::class,'inventory_supplier_id');
    }
}
