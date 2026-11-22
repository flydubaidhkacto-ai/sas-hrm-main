<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Attendance extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'got_late_check_in' => 'boolean',
        'did_early_check_in' => 'boolean',
    ];
    protected $appends = [
        // 'shift_data'
    ];

    static function booted()
    {
        static::created(function ($attendance) {

        });
        static::updated(function ($attendance) {

        });
    }
    function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    function shift()
    {
        return $this->belongsTo(Shift::class)->withTrashed();
    }

    function used_in_out_records()
    {
        return $this->belongsToMany(InOutRecord::class, 'attendances_in_out_records', 'attendance_id', 'in_out_record_id');
    }
    function roster()
    {
        return $this->belongsTo(Roster::class);
    }
    function roster_items()
    {
        return $this->belongsToMany(RosterItem::class, 'attendance_roster_item', 'attendance_id', 'roster_item_id');
    }

    public static function validateOutSidePunch(Request $request, $return_error = false)
    {
        $user = $request->auth_user ?: user();
        if (!config('app.debug')) {
            $min = Setting::duplicate_punch_minutes() ?: 0;
            if (
                InOutRecord::where('user_id', $user->id)
                    ->where('date_time', '<=', now()->addMinutes($min))
                    ->where('date_time', '>=', now()->subMinutes($min))
                    ->exists()
            ) {
                $message = "Cannot punch before 1 minute delay!";
                if ($return_error) {
                    return $message;
                }
                abort(403, $message);
            }
        }
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $restrictedArea = Setting::restrict_attendance_location() ? AllowedPunchRegion::count() : null;
        if (
            $restrictedArea
            && AllowedPunchRegion::isLocationWithin(
                $request->latitude,
                $request->longitude
            ) === false
        ) {
            $message = "Please get inside the allowed area before punching!";
            if ($return_error) {
                return $message;
            }
            abort(403, $message);
        }
        return null;
    }

    function delete_attendnace_upon_late()
    {
        if(
            Setting::delete_attendnace_upon_late()
            && ($this->check_in ?: $this->worked_from)
        ){
            $not_allowed_before_after = Setting::delete_attendnace_upon_late_minutes();
            $checkedIn = ($this->check_in ?: $this->worked_from);

            if ($checkedIn) {
                $checkedInTime = carbon($checkedIn);
                $shiftStart = carbon($this->date . ' ' . $this->shift->start);

                $startWindow = $shiftStart->copy()->subMinutes($not_allowed_before_after);
                $endWindow = $shiftStart->copy()->addMinutes($not_allowed_before_after);

                // Delete if outside the allowed window
                if ($checkedInTime->lt($startWindow) || $checkedInTime->gt($endWindow)) {
                    $this->delete();
                    $this->used_in_out_records()->detach();
                }
            }
        }
    }
}
