<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\InOutRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StudentPunchSms implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $student, public string $type, public InOutRecord $inOutRecord)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $template = $this->type === 'IN' ? Setting::get_student_check_in_sms_formating() : Setting::get_student_check_out_sms_formating();
        $template = str_replace('[Name]', $this->student->name, $template);
        $template = str_replace('[Time]', $this->inOutRecord->date_time->format('h:i A'), $template);
        if($this->student->phone_number) {
            SMSApi::send($this->student->phone_number, $template);
        }
    }
}
