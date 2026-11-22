<?php

namespace App\Jobs;

use App\Actions\EmployeePunched;
use App\Http\Controllers\ReportController;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProccessInOutRecords implements ShouldQueue
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
        $this->handle_auto_checkout();

        $records = InOutRecord::query()
            ->limit(1500)
            ->whereHas('employee', function ($q) {
                $q->where('role', '!=', 'super_admin');
            })
            ->where('added', false)
            ->get();
        // dump($records->count());
        foreach ($records as $record) {
            EmployeePunchedJob::dispatch(
                $record
            );
            $record->update(['added' => true]);
        }
    }
    function handle_auto_checkout()
    {
        $attendance_auto_checkout = Setting::attendance_auto_checkout();
        $attendance_auto_checkout_time = Setting::attendance_auto_checkout_time();
        $attendance_auto_checkout_run_at = Setting::attendance_auto_checkout_run_at();
        $attendance_auto_checkout_day = Setting::attendance_auto_checkout_day();
        if (
            $attendance_auto_checkout
            && $attendance_auto_checkout_time
            && $attendance_auto_checkout_run_at
            && ( // Giving extra time since there could be a lot of data to process
                $attendance_auto_checkout_run_at == now()->format('H:i')
                || $attendance_auto_checkout_run_at == now()->subMinute()->format('H:i')
                || $attendance_auto_checkout_run_at == now()->subMinutes(2)->format('H:i')
                || $attendance_auto_checkout_run_at == now()->subMinutes(3)->format('H:i')
            )
            && in_array($attendance_auto_checkout_day, ['previous', 'today'])
        ) {
            $records = Attendance::query()
                ->whereDate('date', now()->subDay()->format('Y-m-d'))
                ->whereNull('worked_to')
                ->withCount('used_in_out_records')
                ->having('used_in_out_records_count', 1)
                ->latest()
                ->limit(500)
                ->get();
            // dump($records->count());
            foreach ($records as $record) {
                $date = now();
                if ($attendance_auto_checkout_day == 'previous') {
                    $date->subDay();
                }
                try {
                    $times = explode(':', $attendance_auto_checkout_time);
                    $date->setTime($times[0], $times[1]);
                } catch (\Throwable $th) {
                }
                $date = $date->format('Y-m-d H:i:s');
                $check = InOutRecord::firstOrCreate([
                    'user_id' => $record->employee_id,
                    'status' => 0,
                    'date_time' => $date,
                    'original_date_time' => $date,
                    'device_mac' => "Auto-Checkout-by-system",
                    'device_uid' => "Auto-Checkout-by-system",
                    'device_attendance_uid' => "Auto-Checkout-by-system",
                ]);
            }
        }
    }
}
