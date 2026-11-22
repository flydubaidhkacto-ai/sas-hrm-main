<?php

namespace App\Interfaces;

use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Http\Request;

interface LeaveApplicationRepositoryInterface
{
    function apply(Request $request, User $user, User $employee): LeaveApplication|null;
}
