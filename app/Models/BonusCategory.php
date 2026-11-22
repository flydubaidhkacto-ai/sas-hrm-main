<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }
}
