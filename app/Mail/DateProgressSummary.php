<?php

namespace App\Mail;

use App\Models\DateProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DateProgressSummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DateProgress $progress)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->progress->completed
            ? 'Your date invitation was accepted ♥'
            : 'Date invitation was left at step '.$this->progress->last_step);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.date-progress-summary');
    }
}
