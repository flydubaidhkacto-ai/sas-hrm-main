<?php

namespace App\Traits;

use App\Actions\GenerateTotalDutyOfEmployee as ActionsGenerateTotalDutyOfEmployee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\User;
use Carbon\Carbon;

trait GenerateTotalDutyOfEmployee
{
    public function generate_total_duty_of_employee(User $employee, Carbon $from, Carbon $to)
    {
        return ActionsGenerateTotalDutyOfEmployee::execute($employee, $from, $to);
    }
}
