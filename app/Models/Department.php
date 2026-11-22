<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Scope to order departments by their configured `order` column.
     * Falls back to `name` for consistent secondary ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    function super_department()
    {
        return $this->belongsTo(SuperDepartment::class);
    }
    function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    static function initFirst()
    {
        if(self::count() === 0)
        {
            $sup = SuperDepartment::first();
            if(!$sup) $sup = SuperDepartment::create(['name' => "General"]);
            Department::create([
                'name' => "General",
                'super_department_id' => $sup->id
            ]);
        }
    }
}
