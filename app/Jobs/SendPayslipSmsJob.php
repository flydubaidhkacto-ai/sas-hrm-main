<?php

namespace App\Jobs;

use App\Actions\GenerateDailyWorkingData;
use App\Actions\GenerateTotalDutyOfEmployee;
use App\Models\User;
use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SMSQueue;
use App\Actions\SMSApi;
use App\Models\Attendance;
use Carbon\Carbon;

class SendPayslipSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;
    protected $ids;

    /**
     * Create a new job instance.
     */
    public function __construct($month, $ids)
    {
        $this->month = $month;
        $this->ids = $ids;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try{
            $payslips = Payslip::where('month_date', carbon($this->month))
                ->whereIn('user_id', $this->ids)
                ->get();

            $month_formatted = Carbon::parse($this->month)->format('F Y');

            foreach ($payslips as $payslip) {
                $user = $payslip->employee;
                if ($user && $user->phone_number)
                {
                    $from = Carbon::parse($payslip->start_date);
                    $to = Carbon::parse($payslip->end_date);
                    $workindData = GenerateTotalDutyOfEmployee::execute(
                        $user, $from, $to
                    );
                    $overtime_amount = 0;
                    foreach($payslip->earn_heads as $earn_head)
                    {
                        if($earn_head['category'] == 'overtime')
                        {
                            $overtime_amount = $earn_head['total_amount'] ?? $earn_head['amount'] ??  0;
                        }
                    }

                    $message = "Dear {$user->name}, your payslip for {$month_formatted} is ready.
Name: {$user->name}
Designation: {$user->designation?->name}
Department: {$user->department?->name}
Total Present: ".(config('app.customer') === 'ssbd' ? $payslip->ssbd_total_attendance : ($workindData['total_worked_days'] ?? '--' )). "
Total Absent: ".(config('app.customer') === 'ssbd' ? $payslip->ssbd_total_absent : ($workindData['total_absent_days'] ?? '--' )).
(config('app.customer') !== 'ssbd' ? "
OT Hours: ". ($workindData['total_overtime_seconds'] ?? '--') : '') . "
OT Amount: ". ($overtime_amount ?? '--'). "
Total Salary: ". number_format($payslip->total_salary ?? 0, 2, '.', ','). "
For any queries, contact HR.";

                    SMSApi::send(
                        $user->phone_number,
                        $message
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send SMS " . $e);
        }
    }
}
