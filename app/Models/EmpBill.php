<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpBill extends Model
{
    /** @use HasFactory<\Database\Factories\EmpBillFactory> */
    use HasFactory;

    protected $table = 'emp_bills';
    protected $fillable = ['name', 'description', 'emp_bills_category_id', 'date', 'amount', 'user_id'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rel_to_emp_bills_category()
    {
        return $this->belongsTo(EmployeeBillsCategory::class, 'emp_bills_category_id');
    }
}
