<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsentFineRule extends Model
{
    protected $fillable = ['absent_type', 'days', 'fine_type', 'amount'];
}
