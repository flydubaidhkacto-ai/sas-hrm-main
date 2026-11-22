<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $modal;
    public $status;
    public $actionBy;
    public $actionDate;

    public function __construct($modal, $status, $actionBy, $actionDate)
    {
        $this->modal = $modal;
        $this->status = $status;
        $this->actionBy = $actionBy;
        $this->actionDate = $actionDate;
    }

    public function build()
    {
        return $this->view('email.leave_request')
                    ->subject("Leave Request " . ucfirst($this->status));
    }
}
