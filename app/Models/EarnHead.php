<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EarnHead extends Model
{
    use HasFactory;
    static $categories = [
        "basic" => "Basic",
        "allowance" => "Allowance",
        'other' => 'Other',

        "rewards" => "Rewards",
        "overtime" => "Overtime",
        "emp_daily_expenses" => "Employee Daily Expenses",
        'duty_bonuses' => 'Duty Bonus',
    ];
    protected $guarded = [];
}
