<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => 1017,
            'date' => $this->faker->date(),
            'shift_id' => 8,
            'worked_from' => '08:00:00',
            'worked_to' => '17:00:00',
            'working_time' => '09:00:00',
            'overtime_from' => null,
            'overtime_to' => null,
            'overtime_time' => '00:00:00',
            'did_early_check_in' => false,
            'got_late_check_in' => false,
            'system_notes' => 'Employee attended on time and left on time.',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
