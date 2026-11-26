<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalRating extends Model
{
    protected $fillable = ['appraisal_id', 'criterion_id', 'level', 'marks', 'comment'];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(AppraisalCriterion::class);
    }
}
