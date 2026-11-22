<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;
    static $categories = [
        'provident_fund' => "Provident fund",
        "tax" => "Tax",
        "food" => "Food",
        "absent" => "Absent",
        "late" => "Late",
        "other" => "Other",

        "loan" => "Loan",
        "fine" => "Fine",
        'employee_bill' => "Employee bill",
        'ration' => 'Ration',
    ];
    protected $guarded = [];
}
