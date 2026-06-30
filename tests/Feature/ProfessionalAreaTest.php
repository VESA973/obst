<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SiteSetting;
use App\Notifications\ProfessionalResetPasswordNotification;
use App\Notifications\ProfessionalVerifyEmailNotification;
use App\Support\SiteMailerConfigurator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
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

        $this->assertGuest();
        $this->assertTrue($user->is_member);
        $this->assertTrue($user->is_health_professional);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, ProfessionalVerifyEmailNotification::class);
    }

    public function test_professional_registration_shows_confirmation_popup(): void
    {
        Notification::fake();

        $this->followingRedirects()
            ->post(route('professional.register'), [
                'name' => 'Dr Popup',
                'email' => 'popup@example.com',
                'password' => 'password-secure',
                'password_confirmation' => 'password-secure',
                'is_health_professional' => '1',
                'form_started_at' => now()->subSeconds(5)->timestamp,
            ])
            ->assertOk()
            ->assertSee('Compte créé')
            ->assertSee('Confirmez votre email')
            ->assertSee('Un email de confirmation vient de vous être envoyé');
    }

    public function test_professional_registration_requires_health_professional_checkbox(): void
    {
        $this->post(route('professional.register'), [
            'name' => 'Dr Missing',
            'email' => 'missing@example.com',
            'password' => 'password-secure',
            'password_confirmation' => 'password-secure',
            'form_started_at' => now()->subSeconds(5)->timestamp,
        ])
            ->assertSessionHasErrors('is_health_professional', null, 'register');
    }

    public function test_email_verification_link_works_without_authenticated_session(): void
    {
        $user = User::factory()->unverified()->create([
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)
            ->assertRedirect(route('pro'));

        $this->assertGuest();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_professional_can_request_a_new_verification_email_by_registering_again(): void
    {
        Notification::fake();
        User::factory()->unverified()->create([
            'email' => 'retry@example.com',
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->post(route('professional.register'), [
            'name' => 'Dr Retry',
            'email' => 'retry@example.com',
            'password' => 'password-secure',
            'password_confirmation' => 'password-secure',
            'is_health_professional' => '1',
            'form_started_at' => now()->subSeconds(5)->timestamp,
        ])
            ->assertRedirect(route('pro'));

        $user = User::where('email', 'retry@example.com')->firstOrFail();
        Notification::assertSentTo($user, ProfessionalVerifyEmailNotification::class);
        $this->assertSame('Dr Retry', $user->name);
    }

    public function test_site_mailer_maps_ssl_to_smtps_scheme(): void
    {
        SiteSetting::setValue('smtp_mailer', 'smtp');
        SiteSetting::setValue('smtp_encryption', 'ssl');
        SiteSetting::setValue('smtp_host', 'smtp.example.com');
        SiteSetting::setValue('smtp_port', '465');

        SiteMailerConfigurator::apply();

        $this->assertSame('smtp', Config::get('mail.default'));
        $this->assertSame('smtps', Config::get('mail.mailers.smtp.scheme'));
        $this->assertSame('smtp.example.com', Config::get('mail.mailers.smtp.host'));
        $this->assertSame(465, Config::get('mail.mailers.smtp.port'));
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

        Notification::assertSentTo($user, ProfessionalResetPasswordNotification::class);
    }
}
