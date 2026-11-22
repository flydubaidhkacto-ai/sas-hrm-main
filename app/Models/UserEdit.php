<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'new_values',
        'approve',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    public function user_i()
    {
        return $this->belongsTo(User::class);
    }
}
