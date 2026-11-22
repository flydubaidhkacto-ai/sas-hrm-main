<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\LeaveApplication;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeaveApplicationDecisionSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        LeaveApplication::query()
            ->where('send_employee_sms', false)
            ->whereNotNull('final_approved_at')
            ->limit(10)
            ->get()
            ->each(function ($application) {
                $res = $this->mass_data_api($application);
            });
    }
    function mass_data_api(LeaveApplication $application)
    {
        $phone = $application->employee?->phone_number;
        if (!$phone) {
            return 1;
        }
        $message = Setting::get_leave_application_formating();
        if (!$message) return;
        $Formatedtext =  $message;
        $date = "(" . $application->start_date->format('d M, Y') . " - " . $application->end_date->format('d M, Y') . ")";
        $company = company_data();
        $replacements = [
            '[Company]' => $company['name'],
            '[Name]' => $application->employee->name,
            '[Date]' => $date,
            '[Decision]' =>  $application->final_approved_at ? 'approved' : 'rejected',
        ];

        $text = replaceSmsPlaceholders($Formatedtext, $replacements);
        $application->update(['send_employee_sms' => true]);
        SMSApi::send(
            $phone,
            $text
        );
    }
}
