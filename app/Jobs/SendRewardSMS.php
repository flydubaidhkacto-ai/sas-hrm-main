<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\EmployeeReward;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRewardSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public EmployeeReward $employeeReward)
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
        $phone = $this->employeeReward->employee->phone_number;

        if (!$phone) {
            return 1;
        }

        SMSApi::send(
            $phone,
            $this->get_text_body()
        );
    }
    function get_text_body()
    {
        $message = Setting::get_rewards_sms_formating();
        if (!$message) return;
        $formattedText =  $message;
        $company = company_data();
        $replacements = [
            '[Company]' => $company['name'],
            '[Name]' => $this->employeeReward->employee->name,
            '[Date]' => $this->employeeReward->created_at->format('d M, Y'),
            '[Amount]' =>  $this->employeeReward->amount,
            '[Approved]' => ($this->employeeReward->name ? " for " . $this->employeeReward->name : ''),
        ];
        $text =  replaceSmsPlaceholders($formattedText, $replacements);
        return $text;
    }
}
