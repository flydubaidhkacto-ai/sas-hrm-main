<?php

namespace App\Actions;

use App\Models\InOutRecord;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Jobs\StudentPunchSms;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class StudentPunched
{
    public const THREE_HOURS = 10_800;        // 3 h in seconds

    /**
     * Handle a student punch AND refresh the Attendance row.
     */
    public static function execute(InOutRecord $inOutRecord): ?Attendance
    {
        return DB::transaction(static function () use ($inOutRecord) {

            $student = $inOutRecord->employee;
            $now     = $inOutRecord->date_time;                     // Carbon instance

            /*────────────────────────────────────
             * 1️⃣  Fetch + lock today's punches
             *────────────────────────────────────*/
            $recordsToday = $student->in_outs()
                ->whereDate('date_time', $now)
                ->orderBy('date_time')
                ->lockForUpdate()
                ->get();

            if ($recordsToday->isEmpty()) {
                return null;
            }

            $punchCount = $recordsToday->count();           // 1,2,3,…
            $isCheckIn  = $punchCount % 2 === 1;            // odd = IN   even = OUT

            /*────────────────────────────────────
             * 2️⃣   Guardian SMS rules
             *────────────────────────────────────*/
            if ($punchCount === 1) {
                if(!$inOutRecord->sms_notified) {
                    StudentPunchSms::dispatch($student, 'IN', $inOutRecord);
                    $inOutRecord->sms_notified = true;
                    $inOutRecord->save();
                }
            }

            if (! $isCheckIn) {
                $firstIn     = $recordsToday->first()->date_time;
                $alreadySent = $recordsToday
                    ->filter(fn ($r, $idx) =>
                        ($idx + 1) % 2 === 0 &&
                        $r->date_time->diffInSeconds($firstIn) >= self::THREE_HOURS &&
                        $r->id !== $inOutRecord->id
                    )
                    ->isNotEmpty();

                $qualifies = $firstIn->diffInSeconds($now) >= self::THREE_HOURS;

                if (! $alreadySent && $qualifies) {
                    if(!$inOutRecord->sms_notified) {
                        StudentPunchSms::dispatch($student, 'OUT', $inOutRecord);
                        $inOutRecord->sms_notified = true;
                        $inOutRecord->save();
                    }
                }
                else {
                }
            }

            /*────────────────────────────────────
             * 3️⃣  Update / create Attendance
             *────────────────────────────────────*/
            $att = self::refreshAttendance($student->id, $now, $recordsToday);
            if($att) $att->used_in_out_records()->sync($recordsToday->pluck('id'));
            return $att;
        });
    }

    /**
     * Create or update the Attendance row for the given student+day.
     *
     * @param  \Illuminate\Support\Collection  $recordsToday  ordered list of punches
     */
    protected static function refreshAttendance(
        int $userId,
        Carbon $now,
        $recordsToday
    ): ?Attendance {
        // Ensure there is (or create) an Attendance row for this calendar date.
        /* @var \App\Models\Attendance $attendance */
        $attendance = Attendance::firstOrNew([
            'employee_id' => $userId,
            'date'    => $now->format('Y-m-d'),
        ]);

        $attendance->check_in  = $recordsToday->first()->date_time;
        $attendance->check_out = $recordsToday->count() > 1
            ? $recordsToday->last()->date_time
            : null;

        // Compute working seconds limited to the student's shifts.
        $workingSeconds = self::calculateWorkingSeconds(
            $recordsToday,
            $attendance->employee->shifts->map(fn($shift) => $shift->shift) // lazy loads pivots if any
        );

        $attendance->working_time = gmdate('H:i:s', $workingSeconds);
        $attendance->save();
        return $attendance;
    }

    /**
     * Sum seconds actually worked inside the student's shifts
     * minus each shift's break minutes (if overlapped at all).
     *
     * @param  \Illuminate\Support\Collection $punches ordered IN/OUT punches for the day
     * @param  \Illuminate\Support\Collection $shifts  the student's active Shift models
     */
    protected static function calculateWorkingSeconds($punches, $shifts): int
    {
        if ($punches->count() < 2) {
            return 0;                           // no finished interval yet
        }

        // Step 1: build presence intervals (IN➔OUT pairs)
        $intervals = [];
        for ($i = 0; $i < $punches->count() - 1; $i += 2) {
            $intervals[] = [
                'start' => $punches[$i]->date_time,
                'end'   => $punches[$i + 1]->date_time,
            ];
        }

        // If no specific shifts, treat whole day as one shift with 0 break.
        if ($shifts->isEmpty()) {
            $seconds = 0;
            foreach ($intervals as $iv) {
                $seconds += $iv['start']->diffInSeconds($iv['end']);
            }
            return $seconds;
        }

        // Step 2: clip each presence interval to each shift & sum overlap
        $totalSeconds = 0;

        foreach ($shifts as $shift) {
            // Build the shift span for *this* date
            $shiftStart = CarbonImmutable::parse($shift->start)
                ->setDateFrom($intervals[0]['start']);
            $shiftEnd   = CarbonImmutable::parse($shift->end)
                ->setDateFrom($intervals[0]['start']);

            // Support overnight shifts (e.g., 22:00‑06:00)
            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd = $shiftEnd->addDay();
            }

            $overlapSeconds = 0;
            foreach ($intervals as $iv) {
                $begin = $iv['start']->max($shiftStart);
                $end   = $iv['end']->min($shiftEnd);
                if ($begin->lt($end)) {
                    $overlapSeconds += $begin->diffInSeconds($end);
                }
            }

            // Subtract the break once if there was any overlap
            if ($overlapSeconds > 0) {
                $overlapSeconds = max(
                    0,
                    $overlapSeconds - ($shift->break_time_minutes * 60)
                );
            }

            $totalSeconds += $overlapSeconds;
        }
        // dd($totalSeconds, $intervals, $shifts);
        return $totalSeconds;
    }
}
