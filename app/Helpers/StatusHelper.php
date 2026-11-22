<?php

namespace App\Helpers;

class StatusHelper
{
    /**
     * Central status configuration
     */
    private const STATUSES = [

        'approved' => [
            'label'        => 'Approved',
            'color'        => '#2ecc71',
            'bg'           => '#eafaf1',
            'button'       => '#27ae60',
            'subject'      => 'Approved',
            'button_text'  => 'View Details',
        ],

        'rejected' => [
            'label'        => 'Rejected',
            'color'        => '#e74c3c',
            'bg'           => '#fdecea',
            'button'       => '#c0392b',
            'subject'      => 'Rejected',
            'button_text'  => 'View Details',
        ],

        'pending' => [
            'label'        => 'Pending',
            'color'        => '#f39c12',
            'bg'           => '#fff4e5',
            'button'       => '#e67e22',
            'subject'      => 'Pending',
            'button_text'  => 'View Details',
        ],

        'forwarded' => [
            'label'        => 'Forwarded',
            'color'        => '#3498db',
            'bg'           => '#eaf2fb',
            'button'       => '#2980b9',
            'subject'      => 'Forwarded',
            'button_text'  => 'View Details',
        ],

        'updated' => [
            'label'        => 'Updated',
            'color'        => '#9b59b6',
            'bg'           => '#f5eef8',
            'button'       => '#8e44ad',
            'subject'      => 'Updated',
            'button_text'  => 'View Changes',
        ],

        'deleted' => [
            'label'        => 'Deleted',
            'color'        => '#7f8c8d',
            'bg'           => '#f2f2f2',
            'button'       => '#95a5a6',
            'subject'      => 'Deleted',
            'button_text'  => 'View Details',
        ],

        'cancelled' => [
            'label'        => 'Cancelled',
            'color'        => '#95a5a6',
            'bg'           => '#f4f6f7',
            'button'       => '#7f8c8d',
            'subject'      => 'Cancelled',
            'button_text'  => 'View Details',
        ],

        'success' => [
            'label'        => 'Success',
            'color'        => '#2ecc71',
            'bg'           => '#eafaf1',
            'button'       => '#27ae60',
            'subject'      => 'Success',
            'button_text'  => 'View Details',
        ],

        'default' => [
            'label'        => 'Updated',
            'color'        => '#34495e',
            'bg'           => '#ecf0f1',
            'button'       => '#2980b9',
            'subject'      => 'Update',
            'button_text'  => 'View Details',
        ],
    ];

    /**
     * Full mail / notification meta
     */
    public static function mail(?string $status): array
    {
        $status = strtolower(trim($status ?? 'pending'));

        return self::STATUSES[$status]
            ?? array_merge(
                self::STATUSES['default'],
                ['label' => ucfirst($status)]
            );
    }

    /**
     * 🔹 Short helpers (UI / Blade friendly)
     */
    public static function label(?string $status): string
    {
        return self::mail($status)['label'];
    }

    public static function color(?string $status): string
    {
        return self::mail($status)['color'];
    }

    public static function bg(?string $status): string
    {
        return self::mail($status)['bg'];
    }

    public static function button(?string $status): string
    {
        return self::mail($status)['button'];
    }

    public static function subject(?string $status): string
    {
        return self::mail($status)['subject'];
    }

    public static function buttonText(?string $status): string
    {
        return self::mail($status)['button_text'];
    }
}
