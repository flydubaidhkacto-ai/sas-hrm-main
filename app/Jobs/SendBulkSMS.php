<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(public User $employee, public $message)
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
            $this->message
        );
    }
}
