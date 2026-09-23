<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Page Contact : formulaire enregistré en base, notification, et gestion dans Filament.
 */
class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return [
            'topic' => 'partenariat',
            'name' => 'Aline Joseph',
            'email' => 'aline@example.com',
            'phone' => '0694 00 00 00',
            'subject' => 'Proposition d\'atelier',
            'message' => 'Bonjour, nous souhaitons organiser un atelier avec votre association.',
            'consent' => '1',
            ...$this->validAntiBotPayload('contact'),
            ...$overrides,
        ];
    }

    public function test_page_shows_the_form_urgent_numbers_and_real_newsletter(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('action="'.route('contact.send').'"', false)
            ->assertSee('Un partenariat')
            ->assertSee('href="tel:15"', false)
            ->assertSee('href="tel:112"', false)
            ->assertSee('action="'.route('newsletter.subscribe').'"', false)
            ->assertSee('Inscrivez-vous pour suivre les manifestations, ressources et appels à participation.')
            // Plus aucune adresse fictive ni lien mailto de formulaire.
            ->assertDontSee('quinzaine-obstetricale.test')
            // Coordonnées et réseaux masqués tant qu'ils ne sont pas renseignés.
            ->assertDontSee('Nos coordonnées')
            ->assertDontSee('Suivre l\'association', false);
    }

    public function test_configured_details_and_socials_are_displayed(): void
    {
        config(['contact' => ['email' => 'contact@exemple.org', 'phone' => '05 94 00 00 00', 'address' => 'Cayenne, Guyane', 'socials' => ['Facebook' => 'https://facebook.com/exemple']]]);

        $this->get(route('contact'))
            ->assertSee('Nos coordonnées')
            ->assertSee('mailto:contact@exemple.org', false)
            ->assertSee('tel:0594000000', false)
            ->assertSee('Cayenne, Guyane')
            ->assertSee('https://facebook.com/exemple', false);
    }

    public function test_message_is_stored_and_the_association_is_notified(): void
    {
        Mail::fake();
        SiteSetting::setValue('smtp_from_address', 'equipe@exemple.org');

        $this->from(route('contact'))
            ->post(route('contact.send'), $this->payload())
            ->assertRedirect(route('contact').'#formulaire')
            ->assertSessionHas('contact_status');

        $message = ContactMessage::sole();
        $this->assertSame('partenariat', $message->topic);
        $this->assertNull($message->read_at);

        Mail::assertSent(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo('equipe@exemple.org')
            && $mail->hasReplyTo('aline@example.com'));
    }

    public function test_message_is_kept_even_if_the_mail_fails(): void
    {
        SiteSetting::setValue('smtp_from_address', 'equipe@exemple.org');
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->post(route('contact.send'), $this->payload())->assertSessionHas('contact_status');

        $this->assertSame(1, ContactMessage::count());
    }

    public function test_validation_consent_and_anti_bot(): void
    {
        $this->post(route('contact.send'), $this->payload(['consent' => null, 'topic' => 'inconnu', 'message' => 'Court']))
            ->assertSessionHasErrors(['consent', 'topic', 'message'], errorBag: 'contact');

        $this->post(route('contact.send'), $this->payload(['website' => 'spam']))
            ->assertSessionHasErrors('website', errorBag: 'contact');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_reads_messages_and_sees_the_unread_count(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $message = ContactMessage::create(collect($this->payload())->only(['topic', 'name', 'email', 'subject', 'message'])->all());
        ContactMessage::create([...$message->only(['topic', 'name', 'email', 'subject', 'message']), 'read_at' => now()]);

        $this->assertSame('1', ContactMessageResource::getNavigationBadge());

        Livewire::test(ManageContactMessages::class)
            ->assertCanSeeTableRecords([$message])
            ->mountAction(TestAction::make('view')->table($message))
            ->assertMountedActionModalSee('Proposition d\'atelier');

        $this->assertNotNull($message->refresh()->read_at);
        $this->assertNull(ContactMessageResource::getNavigationBadge());

        Livewire::test(ManageContactMessages::class)
            ->selectTableRecords([$message])
            ->callAction(TestAction::make('markUnread')->table()->bulk());

        $this->assertNull($message->refresh()->read_at);
    }
}
