<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Support\AntiBotChallenge;
use App\Support\SiteMailerConfigurator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Formulaire de la page Contact : enregistrement du message et notification par e-mail.
 */
class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        AntiBotChallenge::verify($request, 'contact', 'contact');

        $data = $request->validateWithBag('contact', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'topic' => ['required', Rule::in(array_keys(ContactMessage::TOPICS))],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Merci d\'accepter que vos données soient utilisées pour vous répondre.',
            'message.min' => 'Votre message est un peu court (10 caractères minimum).',
        ]);

        $message = ContactMessage::query()->create(collect($data)->except('consent')->all());

        // Notification à l'association ; le message reste enregistré même si l'envoi échoue.
        $recipient = SiteSetting::getValue('smtp_from_address') ?: config('mail.from.address');

        if (filled($recipient)) {
            try {
                SiteMailerConfigurator::apply();
                Mail::to($recipient)->send(new ContactMessageReceived($message));
            } catch (Throwable $exception) {
                Log::warning('Notification de contact non envoyée : '.$exception->getMessage());
            }
        }

        return back()
            ->with('contact_status', 'Merci ! Votre message a bien été envoyé. Nous vous répondrons dans les meilleurs délais.')
            ->withFragment('formulaire');
    }
}
