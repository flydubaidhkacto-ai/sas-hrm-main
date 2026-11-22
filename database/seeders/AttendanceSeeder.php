<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeId = 1017;
        $shiftId = 8;

        Schema::disableForeignKeyConstraints();

        DB::table('attendances')->truncate();


        Schema::enableForeignKeyConstraints();

        $attendances = [];
        for ($day = 1; $day <= 31; $day++) {
            $attendances[] = [
                'employee_id' => $employeeId,
                'date' => "2025-01-{$day}",
                'shift_id' => $shiftId,
                'worked_from' => '2025-01-' . str_pad($day, 2, '0', STR_PAD_LEFT) . ' 08:00:00',
                'worked_to' => '2025-01-' . str_pad($day, 2, '0', STR_PAD_LEFT) . ' 17:00:00',
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

        // Insert records into the database
        // Attendance::insert($attendances);
    }
}
