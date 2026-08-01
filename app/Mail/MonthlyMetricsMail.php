<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyMetricsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $report
    ) {
    }

    public function envelope(): Envelope
    {
        $client = $this->report['client'];
        $month = $this->report['start_date']->format('F Y');

        return new Envelope(
            subject: "{$client->name} Monthly Metrics | {$month}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'reports.monthly-message',
            with: $this->report,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}