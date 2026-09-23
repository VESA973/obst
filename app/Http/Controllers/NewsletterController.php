<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Support\AntiBotChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Inscription à la lettre d'information depuis le pied de page des Actualités.
 */
class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        AntiBotChallenge::verify($request, 'newsletter', 'newsletter');

        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Merci d\'accepter de recevoir la lettre d\'information.',
        ]);

        // Une adresse déjà inscrite reçoit le même message : on ne révèle pas qui est abonné.
        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => Str::lower($data['email'])],
            ['source' => Str::limit($request->headers->get('referer', ''), 250, '')],
        );

        return back()
            ->with('newsletter_status', 'Merci ! Votre inscription à la lettre d\'information est enregistrée.')
            ->withFragment('newsletter');
    }
}
