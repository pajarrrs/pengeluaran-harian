<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $summary,
        public array $budgetAlerts = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ringkasan Pengeluaran — ' . now()->format('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.daily-summary',
        );
    }
}
