<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notification à l'association : nouveau message reçu via la page Contact.
 * « Répondre » dans la messagerie écrit directement à l'expéditeur.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
            subject: 'Nouveau message ('.$this->contactMessage->topicLabel().') : '.$this->contactMessage->subject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message-received');
    }
}
