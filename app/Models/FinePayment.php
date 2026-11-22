<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinePayment extends Model
{
    use HasFactory;
    protected $guarded = [];
    function fine()
    {
        return $this->belongsTo(Fine::class);
    }
    function user()
    {
        return $this->belongsTo(User::class);
    }
}
