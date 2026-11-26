<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalJobItem extends Model
{
    protected $fillable = ['appraisal_id', 'description', 'is_satisfactory', 'standard'];

    protected $casts = ['is_satisfactory' => 'boolean'];
}
