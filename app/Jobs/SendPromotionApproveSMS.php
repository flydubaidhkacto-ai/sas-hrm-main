<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPromotionApproveSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(public User $employee)
    {
        //
    }

    public function handle()
    {
        $phone = $this->employee->phone_number;

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
        $replacements = [
            '[Name]' => $this->employee->name,
            '[Designation]' => $this->employee->designation ? $this->employee->designation?->name : ' ',
        ];

        $message = Setting::get_promotion_sms_formating();
        if (!$message) return;
        $formattedText =  $message;
        $text = replaceSmsPlaceholders($formattedText, $replacements);
        return $text;
    }
}
