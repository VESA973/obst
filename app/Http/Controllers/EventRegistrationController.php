<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_published, 404);
        abort_if($event->is_paid, 422, 'Les inscriptions payantes se font via le lien externe.');

        if ($event->registration_capacity && $event->registrations()->count() >= $event->registration_capacity) {
            throw ValidationException::withMessages([
                'email' => 'Cet événement est complet.',
            ]);
        }

        $registration = $event->registrations()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('event_registrations')->where('event_id', $event->id)],
            'phone' => ['nullable', 'string', 'max:40'],
        ]));

        $this->configureMailer();

        Mail::to($registration->email)->send(new EventRegistrationConfirmation($registration));

        return back()->with('status', 'Votre inscription a été enregistrée. Un email de confirmation vient de vous être envoyé.');
    }

    private function configureMailer(): void
    {
        $mailer = SiteSetting::getValue('smtp_mailer', config('mail.default', 'log')) ?: config('mail.default', 'log');
        $fromAddress = SiteSetting::getValue('smtp_from_address') ?: config('mail.from.address');
        $fromName = SiteSetting::getValue('smtp_from_name') ?: config('mail.from.name');

        Config::set('mail.default', $mailer);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        if ($mailer !== 'smtp') {
            return;
        }

        Config::set('mail.mailers.smtp.host', SiteSetting::getValue('smtp_host') ?: config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', (int) (SiteSetting::getValue('smtp_port') ?: config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', SiteSetting::getValue('smtp_username') ?: config('mail.mailers.smtp.username'));
        Config::set('mail.mailers.smtp.password', SiteSetting::getValue('smtp_password') ?: config('mail.mailers.smtp.password'));
        Config::set('mail.mailers.smtp.scheme', SiteSetting::getValue('smtp_encryption') ?: null);
    }
}
