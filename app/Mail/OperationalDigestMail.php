<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OperationalDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $summary
     */
    public function __construct(public array $summary) {}

    public function envelope(): Envelope
    {
        $status = $this->summary['executive_status']['label'] ?? 'Operasional';

        return new Envelope(
            subject: '['.config('app.name').'] Ringkasan Operasional — '.$status.' ('.now()->format('d/m/Y').')',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.operational-digest',
            with: ['summary' => $this->summary],
        );
    }
}
