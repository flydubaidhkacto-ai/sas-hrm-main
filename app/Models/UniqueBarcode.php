<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniqueBarcode extends Model
{
    protected $fillable = [
        'model_id', 'type', 'code', 'assigned_id'
    ];
}
