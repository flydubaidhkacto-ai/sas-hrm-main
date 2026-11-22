<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperDepartment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Scope to order super-departments by their configured `order` column.
     * Falls back to `name` for consistent secondary ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    function departments()
    {
        return $this->hasMany(Department::class);
    }
}
