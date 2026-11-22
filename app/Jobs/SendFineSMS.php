<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\Fine;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFineSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Fine $fine)
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
        $phone = $this->fine->employee->phone_number;

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
        $message = Setting::get_fine_sms_formating();
        if (!$message) return;
        $text =  $message;
        $company = company_data();
        $emp_name = $this->fine->employee->name;
        $amount = $this->fine->amount;
        $date =  $this->fine->created_at->format('d M, Y');
        $reson = $this->fine->reason;


        $replacements = [
            '[Company]' => $company['name'],
            '[Name]' => $emp_name,
            '[Time]' => $date,
            '[Amount]' =>  $amount,
            '[Reason]' => $reson,
        ];

        $formattedText = replaceSmsPlaceholders($text, $replacements);
        return $formattedText;
    }
}
