<?php

namespace App\Models;

use App\Actions\EmployeePunched;
use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'attendance_request';
    protected $guarded = [];

    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function approved_event(?User $user = null)
    {
        $user = $user ?? user();
        $att = null;
        if ($this->punch_in) {
            // try{
            $in = InOutRecord::where('user_id', $this->user_id)
                ->where('date_time', $this->punch_in)
                ->first();
            if (!$in) {
                $in = InOutRecord::firstOrCreate([
                    'user_id' => $this->user_id,
                    'device_uid' => 'On response to Att. Req. #' . $this->id,
                    'date_time' => $this->punch_in,
                    'original_date_time' => $this->punch_in,
                    'device_mac' => 1,
                    'device_attendance_uid' => 1,
                    'unique_data' => $this->punch_in . '-' . $this->user_id,
                    'added' => 0,
                    'status' => 1,
                ]);
            }
            $att = \App\Actions\EmployeePunched\Execute::run($in);
            // }
            // catch (\Throwable $th) {}
        }
        if ($this->punch_out) {
            // try{
            $in = InOutRecord::where('user_id', $this->user_id)
                ->where('date_time', $this->punch_out)
                ->first();
            if (!$in) {
                $in = InOutRecord::firstOrCreate([
                    'user_id' => $this->user_id,
                    'device_uid' => 'On response to Att. Req. #' . $this->id,
                    'date_time' => $this->punch_out,
                    'original_date_time' => $this->punch_out,
                    'device_mac' => 1,
                    'device_attendance_uid' => 1,
                    'unique_data' => $this->punch_out . '-' . $this->user_id,
                    'added' => 0,
                    'status' => 0,
                ]);
            }
            $att = \App\Actions\EmployeePunched\Execute::run($in);
            // }
            // catch (\Throwable $th) {}
        }

        if ($att instanceof Attendance) {
            $att->added_manually_by = $user->id;
            $att->duty_type = $this->duty_type;
            $att->save();
            Fine::asp_delete_auto_absent_fines_if_present_later(
                [$att->date],
                $this->user_id
            );
        }
    }

    public function getRequestTypeAttribute()
    {
        if ($this->punch_in && $this->punch_out) {
            return 'IN-OUT';
        }

        if ($this->punch_in) {
            return 'IN';
        }

        if ($this->punch_out) {
            return 'OUT';
        }

        return '-';
    }
}
