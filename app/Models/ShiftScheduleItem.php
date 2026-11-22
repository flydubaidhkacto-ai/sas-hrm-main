<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ShiftScheduleItem extends Model
{
    protected $guarded = [];
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}

