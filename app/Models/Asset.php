<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $guarded = [];

    function asset_category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function asset_request()
    {
        return $this->hasMany(AssetRequest::class);
    }

    function super_department()
    {
        return $this->belongsTo(SuperDepartment::class, 'location_id');
    }
}
