<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaysPerformanceMonth extends Model
{
    use HasFactory;
    protected $guarded = [];

    function performance()
    {
        return $this->belongsTo(PerformanceOfMonth::class);
    }
    function category()
    {
        return $this->belongsTo(PerformanceCategory::class, 'performance_category_id', 'id');
    }
}
