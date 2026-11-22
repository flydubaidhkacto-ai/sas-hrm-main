<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBillsCategory extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeBillsCategoryFactory> */
    use HasFactory;

    protected $table = "employee_bills_categories";

    protected $fillable = ['name', 'is_active'];
}
