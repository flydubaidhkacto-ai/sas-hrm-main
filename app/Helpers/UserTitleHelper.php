<?php

namespace App\Helpers;

use App\Models\User;

class UserTitleHelper
{
    public static function title($gender, $maritalStatus): string
    {

        if ($gender === 'Male') {
            return 'Mr.';
        }

        if ($gender === 'Female') {
            return $maritalStatus === 'Married'
                ? 'Mrs.'
                : 'Ms.';
        }

        return '';
    }

    public static function fullName($name, $gender, $maritalStatus): string
    {
        return trim(self::title($gender, $maritalStatus) . ' ' . $name);
    }
}
