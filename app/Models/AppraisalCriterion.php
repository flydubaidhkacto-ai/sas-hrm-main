<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalCriterion extends Model
{
    protected $table = 'appraisal_criteria';
    protected $fillable = ['code', 'section', 'title', 'low_text', 'mid_text', 'high_text'];
}
