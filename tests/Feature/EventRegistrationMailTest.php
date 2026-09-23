<?php

namespace Tests\Feature;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventRegistrationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_event_registration_sends_confirmation_email(): void
    {
        Mail::fake();

        $event = Event::create([
            'title' => 'Atelier gratuit',
            'event_date' => now()->addWeek()->toDateString(),
            'location' => 'Cayenne',
            'is_published' => true,
        ]);

        $this->post(route('events.register', $event), [
            'name' => 'Marie Test',
            'email' => 'marie@example.com',
            'phone' => '0694000000',
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'email' => 'marie@example.com',
        ]);

        Mail::assertSent(EventRegistrationConfirmation::class, function (EventRegistrationConfirmation $mail): bool {
            return $mail->hasTo('marie@example.com')
                && $mail->registration->event->title === 'Atelier gratuit';
        });
    }
}
