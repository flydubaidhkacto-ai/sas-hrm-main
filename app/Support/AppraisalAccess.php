<?php

namespace App\Support;

use App\Models\User;

/**
 * Appraisal-specific roles. Kept separate from users.role / Role permissions of SAS HRM.
 * users.appraisal_role: supervisor | hod | hr | md  (null = normal employee)
 * super_admin can create appraisals and act on any pending stage (admin override).
 */
class AppraisalAccess
{
    public const ROLES = ['supervisor' => 'Supervisor', 'hod' => 'Head of Department', 'hr' => 'HR', 'md' => 'Managing Director'];

    public static function isAdmin(User $u): bool
    {
        return $u->role === 'super_admin';
    }

    public static function is(User $u, string $role): bool
    {
        return $u->appraisal_role === $role;
    }

    /** May create appraisals / manage setup */
    public static function isHr(User $u): bool
    {
        return self::isAdmin($u) || self::is($u, 'hr');
    }

    public static function seesAll(User $u): bool
    {
        return self::isAdmin($u) || self::is($u, 'hr') || self::is($u, 'md');
    }
}
