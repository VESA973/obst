<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'email' => 'Cet evenement est complet.',
            ]);
        }

        $event->registrations()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('event_registrations')->where('event_id', $event->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]));

        return back()->with('status', 'Votre inscription a ete enregistree.');
    }
}
