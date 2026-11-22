<?php

namespace App\Gates;

use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\Auth;

class Payroll
{
    static function edit(ModelsUser|null $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return in_array(
            'payroll.edit',
            config('roles')[$user->role]['permissions']
        );
    }
    static function show_payslip(ModelsUser|null $user = null, $payslip)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
            || $payslip->user_id == $user->id
        ) return true;
        return in_array(
            'payroll.edit',
            config('roles')[$user->role]['permissions']
        );
    }
}
