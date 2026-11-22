<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataQueryRequest extends Model
{
    protected $guarded = [];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function requestedTo()
    {
        return $this->belongsTo(User::class, 'requested_to');
    }

}
