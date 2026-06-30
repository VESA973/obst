<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfessionalAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_authentication_gate_on_professional_page(): void
    {
        $this->get('/espace-pro')
            ->assertOk()
            ->assertSee('Accédez aux contenus réservés')
            ->assertDontSee('Consulter les dossiers');
    }

    public function test_authenticated_professional_sees_private_content(): void
    {
        $user = User::factory()->create([
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->actingAs($user)
            ->get('/espace-pro')
            ->assertOk()
            ->assertSee('Consulter les dossiers')
            ->assertSee('Réservé pro')
            ->assertDontSee('Créer un compte pro');
    }

    public function test_unverified_professional_does_not_see_private_content(): void
    {
        $user = User::factory()->unverified()->create([
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->actingAs($user)
            ->get('/espace-pro')
            ->assertOk()
            ->assertSee('Confirmez votre email')
            ->assertDontSee('Consulter les dossiers');
    }

    public function test_professional_registration_requires_health_professional_certification_and_sends_verification_email(): void
    {
        Notification::fake();

        $this->post(route('professional.register'), [
            'name' => 'Dr Test',
            'email' => 'dr@example.com',
            'password' => 'password-secure',
            'password_confirmation' => 'password-secure',
            'is_health_professional' => '1',
            'form_started_at' => now()->subSeconds(5)->timestamp,
        ])
            ->assertRedirect(route('pro'));

        $user = User::where('email', 'dr@example.com')->firstOrFail();

        $this->assertTrue($user->is_member);
        $this->assertTrue($user->is_health_professional);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_professional_registration_rejects_honeypot(): void
    {
        $this->post(route('professional.register'), [
            'name' => 'Robot Test',
            'email' => 'robot@example.com',
            'password' => 'password-secure',
            'password_confirmation' => 'password-secure',
            'is_health_professional' => '1',
            'website' => 'https://spam.test',
            'form_started_at' => now()->subSeconds(5)->timestamp,
        ])
            ->assertSessionHasErrors('website', null, 'register');
    }

    public function test_professional_password_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'pro@example.com',
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->post(route('professional.password.email'), [
            'email' => 'pro@example.com',
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
