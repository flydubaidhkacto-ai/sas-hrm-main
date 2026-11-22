<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject($this->data['subject'])
            ->view($this->data['template'] ?? 'email.system')
            ->with([
                'subject'      => $this->data['subject'],
                'body'         => $this->data['body'],
                'url'          => $this->data['url'] ?? null,
                'meta'         => $this->data['meta'] ?? [],
                'status' => isset($this->data['status']) ? $this->data['status'] : null,
                'statusColor'  => $this->data['status_color'] ?? null,
                'statusBg'     => $this->data['status_bg'] ?? null,
                'buttonColor'  => $this->data['button_color'] ?? null,
                'buttonText'   => $this->data['button_text'] ?? null,
            ]);
    }
}
