<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number', 'vendor_id', 'due_date', 'amount_due', 'amount_paid', 'status', 'description'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
