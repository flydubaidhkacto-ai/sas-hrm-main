<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $fillable = ['model_name', 'status', 'visited_section_name', 'operation_data', 'user_id'];

    public function rel_to_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
