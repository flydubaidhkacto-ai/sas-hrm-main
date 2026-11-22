<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    function asset()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
