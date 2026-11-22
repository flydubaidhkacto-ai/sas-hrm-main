<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentShift extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'student_id',
        'shift_id',
    ];

    // Relationship with the User model (student)
    public function student_shift()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Relationship with the Shift model
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
