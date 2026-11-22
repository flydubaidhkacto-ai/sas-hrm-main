<?php 
namespace App\Actions;

use App\Models\PayrollCard;
use App\Models\User;
use Carbon\Carbon;

class GeneratePayrollCard
{
    static function generate (
        User $user, Carbon $date, bool $recreate = false
    ) {
        if(
            !$recreate && PayrollCard::where('user_id', $user->id)->whereMonth('date', $date->month)->first()
        ) return null;
        
    }
}