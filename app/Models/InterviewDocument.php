<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewDocument extends Model
{
    protected $fillable = ['interview_id', 'type', 'path'];

    public function interview_employee()
    {
        return $this->belongsTo(InterviewEmployee::class);
    }
}
