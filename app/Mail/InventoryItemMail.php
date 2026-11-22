<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InventoryItemMail extends Mailable
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
        return $this->view('email.inventory_item_request')
                    ->subject("Inventory Item Request " . ucfirst($this->status));
    }
}
