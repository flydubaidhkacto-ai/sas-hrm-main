<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory, ApprovalProcessModal;
    protected $approval_process_type = 'fine';
    protected $guarded = [];

    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function added_by()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
    function paid_amount_till_now()
    {
        return $this->hasMany(FinePayment::class, 'fine_id');
    }

    static function asp_delete_auto_absent_fines_if_present_later(array $dates, int $user_id)
    {
        if (config('app.customer') === 'asp') {
            foreach ($dates as $date) {
                Fine::query()
                    ->where('reason', 'Auto Absent Fine for ' . carbon($date)->format('d F, Y'))
                    ->where('user_id', $user_id)
                    ->delete();
            }
        }
    }
}
