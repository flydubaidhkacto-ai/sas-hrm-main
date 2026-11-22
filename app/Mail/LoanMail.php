<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $modal;
    public $status;
    public $actionBy;
    public $actionDate;

    /**
     * Create a new message instance.
     */
    public function __construct($modal, $status, $actionBy, $actionDate)
    {
        $this->modal = $modal;
        $this->status = $status;
        $this->actionBy = $actionBy;
        $this->actionDate = $actionDate;
    }


    public function build()
    {
        return $this->view('email.loan_request')
                    ->subject("Loan Request " . ucfirst($this->status));
    }

}
