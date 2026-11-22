<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $guarded = [];
    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function category()
    {
        return $this->belongsTo(BonusCategory::class, 'bonus_category_id');
    }
}
