<?php

namespace App\Jobs;

use App\Mail\SystemMail;
use App\Models\MailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public MailLog $mailLog) {}

public
function handle(): void
{
    try {

        // build mail
        $mail = new SystemMail([
            'subject' => $this->mailLog->subject,
            'body' => $this->mailLog->body,
            'template' => $this->mailLog->meta['template'],
            'url' => $this->mailLog->meta['url'],
            'status' => isset($this->mailLog->meta['status']) ? $this->mailLog->meta['status'] : null,
            'status_color' => $this->mailLog->meta['color'] ?? '#f39c12',
            'status_bg' => $this->mailLog->meta['bg'] ?? '#fff4e5',
            'button_color' => $this->mailLog->meta['button'] ?? '#e67e22',
            'button_text' => $this->mailLog->meta['button_text'] ?? 'View Details',
            'meta' => $this->mailLog->meta ?? [],
        ]);

        // ⭐⭐⭐ ATTACH FILES
        if (!empty($this->mailLog->attachments)) {
            foreach ($this->mailLog->attachments ?? [] as $file) {
                if ($file && file_exists($file)) {
                    $mail->attach($file);
                }
            }
        }

        $mail->from(
            $this->mailLog->from_email ?? config('mail.from.address'),
            $this->mailLog->meta['from_name'] ?? config('mail.from.name')
        );


        Mail::to($this->mailLog->to_email)
            ->cc($this->mailLog->cc_email ?? [])
            ->bcc($this->mailLog->bcc_email ?? [])
            ->send($mail);

        $this->mailLog->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempt' => $this->mailLog->attempt + 1,
        ]);

    } catch (Throwable $e) {

        $this->mailLog->update([
            'status' => 'failed',
            'attempt' => $this->mailLog->attempt + 1,
            'error_message' => $e->getMessage(),
        ]);

        throw $e;
    }
}
}