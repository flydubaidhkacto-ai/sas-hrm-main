<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HalfDayFine extends Model
{
    protected $fillable = ['amount_type', 'deduction_amount', 'minutes', 'created_by'];

    public function rel_to_user() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
