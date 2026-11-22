<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewEmployee extends Model
{
    use HasFactory;
    protected $guarded = [];

    function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    function department()
    {
        return $this->belongsTo(Department::class);
    }

    function Interview_document()
    {
        return $this->hasMany(InterviewDocument::class,'interview_id');
    }
}
