<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FineMail extends Mailable
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
        return $this->view('email.fine_request')
                    ->subject("Fine Request " . ucfirst($this->status));
    }
}
