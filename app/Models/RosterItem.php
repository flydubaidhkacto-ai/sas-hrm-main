<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterItem extends Model
{
    protected $guarded = [];

    // NEW: default order sorting
    protected $casts = [
        'order' => 'integer',
    ];

    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(RosterShift::class, 'roster_shift_id');
    }
    function attendances()
    {
        return $this->belongsToMany(Attendance::class, 'attendance_roster_item', 'roster_item_id', 'attendance_id');
    }
}
