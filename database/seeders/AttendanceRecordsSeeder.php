<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds — synthetic attendance records for development only.
     */
    public function run(): void
    {
        if (config('app.env') === 'production') {
            $this->command->error('This seeder is for development only. Aborting.');
            return;
        }

        $this->command->info('Seeding synthetic attendance records...');

        // Clear existing attendance data
        DB::table('attendances_in_out_records')->delete();
        DB::table('attendances')->delete();
        DB::table('in_out_records')->delete();

        $employees = DB::table('users')->where('role', 'employee')->get();
        $shifts = DB::table('shifts')->get();

        if ($employees->isEmpty()) {
            $this->command->error('No employees found. Please run SyntheticDataSeeder first.');
            return;
        }

        $currentMonth = now()->format('Y-m');
        $today = now();
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $today->month, $today->year);

        $attendanceCount = 0;
        $recordCount = 0;

        foreach ($employees as $employee) {
            // Assign a random shift to each employee
            $shift = $shifts->random();

            // Generate attendance for working days of the current month
            for ($day = 1; $day <= min($daysInMonth, $today->day); $day++) {
                $date = sprintf('%s-%02d', $currentMonth, $day);
                $carbonDate = \Carbon\Carbon::parse($date);
                $dayOfWeek = $carbonDate->dayOfWeek;

                // Skip weekends (Saturday=6, Sunday=0) for most employees
                // Some shifts work on weekends, so we allow some weekend attendance
                if ($dayOfWeek === 0 && $shift->id !== 4) {
                    continue; // Skip Sunday for non-night shifts
                }

                // Randomly skip some days (simulating leave/absence) - 5% chance
                if (mt_rand(1, 100) <= 5) {
                    continue;
                }

                // Generate check-in and check-out times based on shift
                $checkInTime = $this->generateCheckInTime($shift);
                $checkOutTime = $this->generateCheckOutTime($shift, $checkInTime);

                $checkInDateTime = $carbonDate->copy()->setTimeFromTimeString($checkInTime);
                $checkOutDateTime = $carbonDate->copy()->setTimeFromTimeString($checkOutTime);

                // Calculate working time
                $workingMinutes = $checkInDateTime->diffInMinutes($checkOutDateTime);
                $workingHours = floor($workingMinutes / 60);
                $workingMinutesRemainder = $workingMinutes % 60;
                $workingTime = sprintf('%02d:%02d:00', $workingHours, $workingMinutesRemainder);

                // Determine if late check-in
                $shiftStart = \Carbon\Carbon::parse($shift->start);
                $isLate = $checkInDateTime->gt($shiftStart->copy()->addMinutes(15)); // 15 min grace period

                // Create attendance record
                $attendanceId = DB::table('attendances')->insertGetId([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'shift_id' => $shift->id,
                    'worked_from' => $checkInDateTime->format('Y-m-d H:i:s'),
                    'worked_to' => $checkOutDateTime->format('Y-m-d H:i:s'),
                    'working_time' => $workingTime,
                    'did_early_check_in' => false,
                    'got_late_check_in' => $isLate,
                    'did_early_check_out' => false,
                    'summary_sms_notified' => false,
                    'admin_summary_sms_notified' => false,
                    'is_in_roster' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $attendanceCount++;

                // Create in-out records
                $inRecordId = DB::table('in_out_records')->insertGetId([
                    'user_id' => $employee->id,
                    'status' => 'IN',
                    'date_time' => $checkInDateTime->format('Y-m-d H:i:s'),
                    'device_uid' => 'DEV-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'device_name' => 'Main Entrance',
                    'added' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $outRecordId = DB::table('in_out_records')->insertGetId([
                    'user_id' => $employee->id,
                    'status' => 'OUT',
                    'date_time' => $checkOutDateTime->format('Y-m-d H:i:s'),
                    'device_uid' => 'DEV-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'device_name' => 'Main Entrance',
                    'added' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $recordCount += 2;

                // Link in-out records to attendance
                DB::table('attendances_in_out_records')->insert([
                    'attendance_id' => $attendanceId,
                    'in_out_record_id' => $inRecordId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('attendances_in_out_records')->insert([
                    'attendance_id' => $attendanceId,
                    'in_out_record_id' => $outRecordId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("  Seeded $attendanceCount attendance records with $recordCount in-out records.");
        $this->command->info('Attendance records seeding completed.');
    }

    /**
     * Generate a check-in time based on the shift with some randomness
     */
    private function generateCheckInTime($shift): string
    {
        $shiftStart = \Carbon\Carbon::parse($shift->start);
        
        // 80% chance of on-time check-in (within 15 min grace period)
        // 20% chance of late check-in (15-45 min late)
        if (mt_rand(1, 100) <= 80) {
            $minutesOffset = mt_rand(-10, 15);
        } else {
            $minutesOffset = mt_rand(16, 45);
        }

        $checkIn = $shiftStart->copy()->addMinutes($minutesOffset);
        return $checkIn->format('H:i:s');
    }

    /**
     * Generate a check-out time based on the shift and check-in time
     */
    private function generateCheckOutTime($shift, $checkInTime): string
    {
        $shiftEnd = \Carbon\Carbon::parse($shift->end);
        
        // Handle overnight shifts
        if ($shiftEnd->lt(\Carbon\Carbon::parse($shift->start))) {
            $shiftEnd = $shiftEnd->copy()->addDay();
        }

        // Add break time
        $breakTime = $shift->break_time_minutes ?? 60;
        
        // 70% chance of normal check-out
        // 30% chance of early or late check-out
        if (mt_rand(1, 100) <= 70) {
            $minutesOffset = mt_rand(-10, 10);
        } elseif (mt_rand(1, 100) <= 50) {
            $minutesOffset = mt_rand(-45, -15); // Early check-out
        } else {
            $minutesOffset = mt_rand(15, 60); // Late check-out (overtime)
        }

        $checkOut = $shiftEnd->copy()->addMinutes($minutesOffset);
        return $checkOut->format('H:i:s');
    }
}
