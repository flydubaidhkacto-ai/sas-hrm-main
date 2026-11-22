<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventorySupplier extends Model
{
    protected $fillable = ['name', 'email', 'phone_number', 'status', 'paid_amount'];
}
