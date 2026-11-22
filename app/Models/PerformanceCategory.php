<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean'
    ];
    function performances()
    {
        return $this->hasMany(PerformanceOfMonth::class);
    }
    static function active()
    {
        return self::query()->where('is_active', 1);
    }
}
