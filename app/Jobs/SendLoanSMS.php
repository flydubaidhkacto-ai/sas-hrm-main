<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\Loan;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLoanSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Loan $loan)
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
        $phone = $this->loan->employee->phone_number;

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
        $company = company_data();
        $message = Setting::get_loan_sms_formating();
        if (!$message) return;
        $formattedText =  $message;
        $replacements = [
            '[Company]' => $company['name'],
            '[Name]' => $this->loan->employee->name,
            '[Date]' => $this->loan->created_at->format('d M, Y'),
            '[Amount]' =>  $this->loan->amount,
            '[Approved]' => $this->loan->approved_at ? 'approved.' : 'rejected.',
        ];
        $text = replaceSmsPlaceholders($formattedText, $replacements);
        return $text;
    }
}
