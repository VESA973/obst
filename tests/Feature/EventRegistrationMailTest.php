<?php

namespace Tests\Feature;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use App\Models\User;
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

    public function test_admin_can_export_event_registrations_as_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'title' => 'Atelier CSV',
            'event_date' => now()->addWeek()->toDateString(),
            'is_published' => true,
        ]);
        $event->registrations()->create([
            'name' => 'Jean Export',
            'email' => 'jean@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.registrations.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('Atelier CSV', $response->streamedContent());
        $this->assertStringContainsString('jean@example.com', $response->streamedContent());
    }
}
