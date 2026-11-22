<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItemRequest extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'inventory_item_request';

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryItemRequestItems::class);
    }

    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'inventory_item_request_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // filter
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['apply_date'])) {
            $query->whereDate('created_at', $filters['apply_date']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public static function getItemBarcodes($request_item_id)
    {
        return UniqueBarcode::query()
            ->where('inventory_item_request_item_id', $request_item_id)
            ->get();
    }
}
