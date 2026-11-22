<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendLateWarningSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $q = Attendance::query()
            ->where('got_late_check_in', true)
            ->whereNull('late_notified_via_sms_at')
            ->whereDate('date', '>=', today()->subDays(3)->format('Y-m-d') . ' 00:00')
            ->withCount('used_in_out_records')
            ->whereHas('employee', function ($q) {
                $q->whereHas('rel_to_sms_notifications', function ($q) {
                    $q->where('types', 'late');
                });
            })
            ->limit(20)
            ->get();

        $q->each(function ($attendance) {
            $res = $this->mass_data_api($attendance);
            sleep(1);
        });
    }
    function get_text_body(Attendance $attendance)
    {
        $shift_start = carbon($attendance->date . ' ' . $attendance->shift->start);
        /**
         * TODO :  Log::info($shift_start . "\n"); Log::info($attendance->worked_from);
         * run the both command and you will see there is no data in worked_form column
         */
        $seconds = $shift_start
            ->diffInSeconds(carbon($attendance->worked_from));
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        $timeFormatted = ""; // sprintf("%02d:%02d", $hours, $minutes);
        $timeFormatted = ($hours > 0) ? $hours . " Hours " : "";
        $timeFormatted .= ($minutes > 0) ? $minutes . " Minutes " : "";
        $timeFormatted .= ($seconds > 0) ? $seconds . " Seconds " : "";

        $date = carbon($attendance->date)->format('d M, Y');
        if ($date === now()->format('d M, Y')) {
            $date = "Today";
        }
        $company = company_data();

        $message = Setting::get_warning_sms_formating();
        if (!$message) return;
        $templatedText = $message;

        $replacements = [
            '[Company]' => $company['name'] ?? 'ProHR',
            '[Date]' => $date,
            '[TimeFormatted]' =>  $timeFormatted,
            '[DMD]' => 'Warning From HR',
        ];

        $text =  replaceSmsPlaceholders($templatedText, $replacements);
        return $text;
    }
    function mass_data_api(Attendance $attendance)
    {
        $phone = $attendance->employee?->phone_number;
        if (!$phone) {
            return 1;
        }

        $attendance->update(['late_notified_via_sms_at' => now()]);

        $this->get_text_body($attendance);
        try {
            SMSApi::send(
                $phone,
                $this->get_text_body($attendance)
            );
        } catch (\Exception $e) {
        }

        return null;
    }
}
