<?php

namespace App\Mail;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EventRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EventRegistration $registration)
    {
        $this->registration->loadMissing('event');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation inscription - '.$this->registration->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-registration-confirmation',
        );
    }

    public function attachments(): array
    {
        $path = $this->registration->event->image_path;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return [];
        }

        return [
            Attachment::fromPath(Storage::disk('public')->path($path))
                ->as('flyer-'.$this->registration->event->id.'.'.pathinfo($path, PATHINFO_EXTENSION)),
        ];
    }
}
