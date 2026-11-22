<?php

namespace App\Http\Controllers;


use App\Actions\EmployeePunched;
use App\Actions\EmployeePunched\Execute;
use App\Actions\GeneratePayslip\Generate;
use App\Actions\EmployeePunched\TimesheetProcessor;
use App\Jobs\CheckEmployeeReward;
use App\Jobs\InitAutoAbsentFineEmployees;
use App\Jobs\SendLateWarningSMS;
use App\Models\Attendance;
use App\Models\AutoReward;
use App\Models\EmployeeReward;
use App\Models\InOutRecord;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\ProvidentFundPayment;
use App\Models\Setting;
use App\Models\SuperDepartment;
use App\Traits\GenerateTotalDutyOfEmployee;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserZkDeviceFingerprint;
use App\Models\ZktecoCloud;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use CheckAndVerifyLicense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function test()
    {
        abort_if(!role_can('superadmin'), 403);
        dump(config('app'));

        $inouts = InOutRecord::where('user_id', 1001)
            ->where('date_time', '>', '2025-09-30 00:00')
            // ->where('date_time', '<', '2025-10-03 23:59:00')
            ->orderBy('date_time', 'asc')
            ->get();

        $inouts->each(function ($in) {
            $att = Execute::run($in);
            dump(
                $in->date_time->format('Y-m-d H:i') . ' - check in '
                . $att?->check_in?->format('Y-m-d H:i') . ' - check out '
                . $att?->check_out?->format('Y-m-d H:i') . ' - worked '
                . $att?->working_time
            );
        });
    }

    /**
     * Generate attendance from a collection of Carbon timestamps.
     *
     * @param Collection|array $punches Collection of Carbon instances (or array)
     * @param int $dayStartHour Workday start hour (0-23). Punches with hour < this are assigned to previous day.
     * @param int $maxShiftHours Max allowed hours per session/day (default 15).
     * @param int $minGapMinutes Minimum minutes between two punches to be considered a legit pair (default 5).
     * @return array
     */
    function generateAttendanceFromPunches($punches, int $dayStartHour = 5, int $maxShiftHours = 15, int $minGapMinutes = 5): array
    {
        // Normalize to Collection of Carbons
        $punches = collect($punches)
            ->filter() // drop nulls
            ->map(function ($p) {
                return $p instanceof Carbon ? $p->copy() : Carbon::parse($p);
            })
            ->sort(); // ascending by timestamp

        // 1) Assign each punch to a single logical workday and group
        $byWorkday = [];
        foreach ($punches as $p) {
            // If punch hour is less than dayStartHour, it's part of previous day (night shift)
            $workday = ($p->hour < $dayStartHour) ? $p->copy()->subDay()->toDateString() : $p->toDateString();
            $byWorkday[$workday][] = $p;
        }

        // 2) Process each workday independently
        $result = [];
        $maxGapMinutes = $maxShiftHours * 60;

        foreach ($byWorkday as $workday => $list) {
            // ensure sorted
            $list = collect($list)->sort()->values();
            $n = $list->count();
            $i = 0;
            $pairs = [];
            $dayAutoCheckout = false;
            $totalHours = 0.0;

            while ($i < $n) {
                $in = $list[$i];

                // If there's no next punch -> missing checkout
                if ($i + 1 >= $n) {
                    $out = $in->copy()->addHours($maxShiftHours);
                    $auto = true;
                    $i++; // consume only the 'in'
                } else {
                    $outCandidate = $list[$i + 1];
                    $diffMin = $in->diffInMinutes($outCandidate);

                    // accidental double-tap — skip the in and continue
                    if ($diffMin < $minGapMinutes) {
                        $i++; // skip this small noise
                        continue;
                    }

                    // If the pair duration is within allowed shift -> accept as pair
                    if ($diffMin <= $maxGapMinutes) {
                        $out = $outCandidate;
                        $auto = false;
                        $i += 2; // consumed both punches
                    } else {
                        // gap is too large -> likely missing checkout for 'in'
                        // treat this in as having auto-checkout at maxShiftHours
                        $out = $in->copy()->addHours($maxShiftHours);
                        $auto = true;
                        $i++; // do not consume the far-away next punch; it's for a later session
                    }
                }

                // Calculate hours, capping per session at maxShiftHours
                $hours = $in->floatDiffInHours($out);
                if ($hours > $maxShiftHours) {
                    $hours = $maxShiftHours;
                }
                $hours = round($hours, 2);

                $pairs[] = [
                    'in'         => $in->toDateTimeString(),
                    'out'        => $out->toDateTimeString(),
                    'hours'      => $hours,
                    'auto_checkout' => $auto,
                ];

                $dayAutoCheckout = $dayAutoCheckout || $auto;
                $totalHours += $hours;
            }

            // Cap total hours per day
            if ($totalHours > $maxShiftHours) {
                $totalHours = $maxShiftHours;
            }

            $result[$workday] = [
                'pairs'       => $pairs,
                'total_hours' => round($totalHours, 2),
                'auto_checkout'=> $dayAutoCheckout,
            ];
        }

        return $result;
    }


    function func()
    {
        // regenerate asp provident fund
        // User::active_employees()
        //     ->whereHas('payroll_card')
        //     ->chunk(100, function($emps) {
        //     foreach($emps as $emp) {
        //         $firstFund = ProvidentFundPayment::where('user_id', $emp->id)
        //             ->orderBy('month_date', 'asc')
        //             ->first();
        //         $fundAmount = $emp->payroll_card->deductions
        //             ->where('category', 'provident_fund')
        //             ->first()?->amount ?: 0;
        //         $rate = Setting::provident_fund_interest_rate();
        //         if($firstFund && $fundAmount) {
        //             ProvidentFundPayment::where('user_id', $emp->id)
        //                 ->where('id', '!=', $firstFund->id)
        //                 ->orderBy('month_date', 'asc')
        //                 ->get()
        //                 ->each(function ($fund) use($fundAmount, &$firstFund, $rate) {
        //                     $newBalance = $fundAmount + $firstFund->balance;
        //                     $closing_fund_balance = $firstFund->balance_with_interest;
        //                     $interest_amount = ($closing_fund_balance * $rate / 12 / 100);
        //                     $sumOfPreviousFundInterests = ProvidentFundPayment::query()
        //                                     ->where('user_id', $fund->user_id)
        //                                     ->where('month_date', '<', $fund->month_date)
        //                                     ->sum('interest_amount');
        //                     $fund->update([
        //                         'balance' => $newBalance,
        //                         'interest_amount' => $interest_amount,
        //                         'interest' => $rate,
        //                         'balance_with_interest' => round(
        //                             $interest_amount + $sumOfPreviousFundInterests + $newBalance, 2
        //                         ),
        //                     ]);
        //                     $firstFund = $fund;
        //                 });
        //         }
        //     }
        // });

        // get_attendance_from_zkteco_cloud_archive(['2025-08-31']);
        // \App\Models\InOutRecord::where('date_time', '>', '2025-10-02 00:00')->chunk(1000, function ($items) {
        //     foreach ($items as $item) {
        //         dump(\App\Actions\EmployeePunched\Execute::run($item));
        //     }
        // });
        // $zk_fingers = UserZkDeviceFingerprint::encodeAllToBase64(
        //     UserZkDeviceFingerprint::query()
        //         ->where('user_id', 1002)
        //         ->get()
        // );
        // $all_fingers_data = [];
        // foreach ($zk_fingers as $zk_finger) {
        //     $all_fingers_data[] = $zk_finger['fingerprint_data'];
        // }
        // // $all_fingers_data = array_unique($all_fingers_data);
        // dd(
        //     $all_fingers_data
        // );
    }
}
