<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalReview extends Model
{
    protected $fillable = [
        'appraisal_id', 'stage', 'reviewer_id', 'marks', 'result_band',
        'recommendation', 'increment_steps', 'comment', 'signed_at',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
