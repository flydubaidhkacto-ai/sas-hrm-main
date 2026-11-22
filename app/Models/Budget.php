<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'budget_limit', 'amount_spent', 'description'];

    public function remainingAmount()
    {
        return $this->budget_limit - $this->amount_spent;
    }
}
