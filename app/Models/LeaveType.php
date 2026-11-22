<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_paid' => 'boolean',
        'affects_leave_balance' => 'boolean',
    ];

    static function earn_leave()
    {
        $data = [
            'name' => 'Earn Leave',
            'is_editable' => false,
        ];
        return self::query()->firstOrCreate($data, $data);
    }
}
