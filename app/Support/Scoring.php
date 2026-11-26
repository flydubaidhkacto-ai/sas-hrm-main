<?php

namespace App\Support;

/**
 * Result band, recommendation and increment steps by marks (form's table).
 * Edit here if your policy differs.
 */
class Scoring
{
    public const RECOMMENDATIONS = [
        'Promotion',
        'Not recommended for promotion',
        'Only Increment',
        'No Increment',
    ];

    public static function band(float $marks): array
    {
        return match (true) {
            $marks <= 40 => ['label' => 'Poor',          'recommendation' => 'Not recommended for promotion', 'steps' => 1],
            $marks <= 60 => ['label' => 'Average',       'recommendation' => 'Not recommended for promotion', 'steps' => 2],
            $marks <= 80 => ['label' => 'Above Average', 'recommendation' => 'Only Increment',                'steps' => 3],
            $marks <= 90 => ['label' => 'High Average',  'recommendation' => 'Promotion',                     'steps' => 4],
            default      => ['label' => 'Excellent',     'recommendation' => 'Promotion',                     'steps' => 4],
        };
    }
}
