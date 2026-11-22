<?php

namespace App\Interfaces;

use App\Models\StationLeaveApplication;
use App\Models\User;
use Illuminate\Http\Request;

interface StationLeaveApplicationRepositoryInterface
{
    function apply(array $data, User $user, User $employee): StationLeaveApplication|null;
}
