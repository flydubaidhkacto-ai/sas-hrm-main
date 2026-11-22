<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LongAbsentUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateLongAbsentReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // logic to generate long absent report
        $longAbsentDays = config('services.long_absent_days', 0);
        if ($longAbsentDays <= 0) {
            return; // Feature disabled
        }
        LongAbsentUser::query()->delete(); // Clear previous records
        // Fetch users and their attendance records
        User::active_employees()
            ->where('join_date', '<=', now()->subDays($longAbsentDays))
            ->chunk(100, function ($users) use ($longAbsentDays) {
                foreach ($users as $user) {
                    $this->processUser($user, $longAbsentDays);
                }
            });
    }
    function processUser(User $user, int $longAbsentDays): ?array
    {
        $attendancesOfTheLastFixedDays = $user->attendance()
            ->where('date', '>=', carbon()->subDays($longAbsentDays))
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();
        // he was present in the last days, so skip!
        if(count($attendancesOfTheLastFixedDays)) return null;

        // 2. Collect leave ranges
        $leaves = LeaveApplication::final_approved()
            ->where('employee_id', $user->id)
            ->get()
            ->flatMap(function ($leave) {
                if ($leave->applied_dates) {
                    return collect($leave->applied_dates);
                }
                return collect(Carbon::parse($leave->start_date)
                    ->daysUntil(Carbon::parse($leave->end_date)))
                    ->map->toDateString();
            })
            ->toArray();
        // he had approved leave in the last days, so skip!
        $recentDates = generateDateRange(carbon()->subDays($longAbsentDays), now());
        if(count(array_intersect($recentDates, $leaves)) > 0) return null;


        // 3. Resolve weekends from shift
        $weekends = [];
        for ($i = 0; $i < 7; $i++) {
            $tmpDay = strtolower(jddayofweek($i, 1));
            if (!$user->shift?->{$tmpDay}) {
                $weekends[] = $tmpDay;
            }
        }

        $absents = [];
        $join_date = carbon($user->join_date);
        $lastAtt = $user->attendance()
            ->orderBy('date', 'desc')
            ->first();
        $lastDate = $user->attendance()
            ->orderBy('date', 'asc')
            ->first()?->date;
        if(!$lastDate) $lastDate = $join_date->format('Y-m-d');
        $lastDate = carbon($lastDate);

        $absentFound = false;
        // 4. Walk backwards day by day until attendance or join_date
        for ($date = Carbon::yesterday(); $date->gte($lastDate); $date->subDay()) {
            if($absentFound) break;

            $dow = strtolower($date->format('l'));
            if (
                in_array($dow, $weekends) ||
                in_array($date->toDateString(), $leaves)
            ) {
                // dump("weekend/leave: " . $date->toDateString());
                continue; // skip leave days
            }
            if($user->attendance()->where('date', $date)->exists()) break;
            // otherwise absent
            $absents[] = $date->toDateString();
            $absentFound = count($absents) >= $longAbsentDays;
        }
        $absentDates = array_reverse($absents);

        // 5. Save only if absence >= threshold
        if (count($absentDates) >= $longAbsentDays) {
            // dd($absentDates, $lastAtt);
            LongAbsentUser::create([
                'user_id' => $user->id,
                'start_date' => $lastAtt ? $lastAtt->date : $absentDates[0],
                'end_date'   => end($absentDates),
                'total_days' => count($absentDates),
                'dates'      => $absentDates,
            ]);
        }

        return $absentDates;
    }

}
