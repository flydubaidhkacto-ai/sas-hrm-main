<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;
    protected $guarded = [];
    static $default_paid = 30;
    static $default_unpaid = 30;

    function user()
    {
        return $this->belongsTo(User::class);
    }

    static function employee_balance($user_id, $type_id, $year = null)
    {
        if ($year == null) {
            $year = date('Y');
        }

        // Leave types that don't affect leave balance (e.g., Work From Home)
        // always return zero balance — they are not tracked as consumable leave
        $type = LeaveType::find($type_id);
        if ($type && !$type->affects_leave_balance) {
            return [
                'limit' => 0,
                'used' => 0,
                'left' => 0,
            ];
        }

        $balance = Setting::emp_leave_balance($user_id, $type_id, $year);

        $used = LeaveApplication::final_approved()
            ->where('employee_id', $user_id)
            ->whereRaw('YEAR(start_date) = ' . $year)
            ->whereHas('leave_type', fn($q) => $q->where('id', $type_id))
            ->sum('duration_count');

        $paid_left = $balance - $used;
        return [
            'limit' => $balance,
            'used' => $used,
            'left' => $paid_left,
        ];
    }
}
