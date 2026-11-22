<?php

namespace App\Gates;

use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\Auth;

class Attendance
{
    static function view(ModelsUser|null $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return in_array(
            'attendance.view',
            config('roles')[$user->role]['permissions']
        );
    }
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
            'attendance.edit',
            config('roles')[$user->role]['permissions']
        );
    }
    static function delete(ModelsUser|null $user = null, ModelsUser|null $target_user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return in_array(
            'users.delete',
            config('roles')[$user->role]['permissions']
        );
    }
}
