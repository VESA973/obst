<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ProfessionalVerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage())
            ->subject('Confirmez votre compte professionnel')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre compte professionnel a bien été créé.')
            ->line('Pour accéder à l’espace professionnel, confirmez votre adresse email en cliquant sur le bouton ci-dessous.')
            ->action('Confirmer mon email', $url)
            ->line('Ce lien est valable pendant 60 minutes.')
            ->salutation('La Quinzaine Obstétricale');
    }
}
