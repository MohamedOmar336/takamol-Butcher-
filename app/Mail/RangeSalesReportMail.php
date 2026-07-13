<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RangeSalesReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    public function envelope(): Envelope
    {
        $start = $this->stats['start_date'];
        $end = $this->stats['end_date'];
        return new Envelope(
            subject: 'تقرير مبيعات الفترة من ' . $start . ' إلى ' . $end . ' | Sales Period Report',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.range_report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
