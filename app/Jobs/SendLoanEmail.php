<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanMail;
use App\Models\User;

class SendLoanEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $modal;
    protected $status;
    protected $actionBy;
    protected $actionDate;

    public function __construct($modal, $status, $actionBy, $actionDate)
    {
        $this->modal = $modal;
        $this->status = $status;
        $this->actionBy = $actionBy;
        $this->actionDate = $actionDate;
    }


    public function handle()
    {
        try{
            $user = $this->modal->employee;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new LoanMail(
                    $this->modal,
                    $this->status,
                    $this->actionBy,
                    $this->actionDate
                ));
            }
        } catch(\Throwable $th) {}
    }
}
