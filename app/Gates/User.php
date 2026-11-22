<?php

namespace App\Gates;

use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\Auth;

class User
{
    static function view(ModelsUser|null $user = null, ModelsUser|null $target_user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
            || ($target_user && $user->id == $target_user->id)
        ) return true;
        return in_array(
            'users.view',
            config('roles')[$user->role]['permissions']
        );
    }
    static function edit(ModelsUser|null $user = null, ModelsUser|null $target_user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
            || ($target_user && $user->id == $target_user->id)
        ) return true;
        return in_array(
            'users.edit',
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
    static function terminate(ModelsUser|null $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return in_array(
            'users.terminate',
            config('roles')[$user->role]['permissions']
        );
    }
    static function reactivate(ModelsUser|null $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return in_array(
            'users.reactivate',
            config('roles')[$user->role]['permissions']
        );
    }
    static function edit_company(ModelsUser|null $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return false;
        if (
            $user->is_super_admin()
        ) return true;
        return false;
    }
}
