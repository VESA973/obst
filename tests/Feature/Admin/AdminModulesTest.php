<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\SiteConfiguration;
use App\Filament\Pages\SitePages;
use App\Filament\Resources\EventRegistrations\Pages\ManageEventRegistrations;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Professionals\Pages\ManageProfessionals;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Event;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Sections du panneau hors articles : connexion, configuration, pages, agenda, comptes.
 */
class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_through_the_panel(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@example.com', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticated();
    }

    public function test_non_admin_cannot_login_through_the_panel(): void
    {
        User::factory()->create(['email' => 'member@example.com', 'is_admin' => false]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'member@example.com', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_configuration_is_saved_and_secrets_are_kept_when_left_blank(): void
    {
        config(['app.url' => 'https://laquinzaineobstetricale.fr']);
        SiteSetting::setValue('smtp_password', 'ancien-secret');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SiteConfiguration::class)
            ->assertSchemaStateSet(['google_redirect_uri' => 'https://laquinzaineobstetricale.fr/professionnels/google/retour'])
            ->fillForm([
                'maintenance_enabled' => true,
                'maintenance_message' => 'Retour dans une heure.',
                'smtp_host' => 'smtp.example.com',
                'smtp_password' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(SiteSetting::maintenanceEnabled());
        $this->assertSame('Retour dans une heure.', SiteSetting::getValue('maintenance_message'));
        $this->assertSame('smtp.example.com', SiteSetting::getValue('smtp_host'));
        $this->assertSame('ancien-secret', SiteSetting::getValue('smtp_password'));
    }

    public function test_admin_can_configure_public_page_header_and_menu_label(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SitePages::class)
            ->fillForm([
                'pages.about.menu_label' => 'Qui sommes nous',
                'pages.about.eyebrow' => 'Association',
                'pages.about.title' => 'Notre equipe et notre histoire',
                'pages.about.description' => 'Texte configure depuis le backend.',
                'pages.about.title_size' => 'large',
                'pages.about.show_in_menu' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Qui sommes nous')
            ->assertSee('Association')
            ->assertSee('Notre equipe et notre histoire')
            ->assertSee('Texte configure depuis le backend.')
            ->assertSee('assoc-title-large');
    }

    public function test_public_pages_have_their_own_default_header_images(): void
    {
        $this->get(route('actions'))
            ->assertOk()
            ->assertSee('has-hero-image')
            ->assertSee('/images/page-headers/actions.webp', false);

        $this->get(route('about'))->assertSee('/images/page-headers/about.webp', false);
    }

    public function test_public_navigation_uses_association_submenu_and_hides_search(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('nav-submenu', false)
            ->assertSee('Nos actions')
            ->assertSee('Santé de la femme')
            ->assertDontSee('Recherche');
    }

    public function test_replacing_an_event_flyer_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/flyers/ancien.png', 'ancien');
        $event = Event::create(['title' => 'Atelier', 'image_path' => 'events/flyers/ancien.png', 'is_published' => true]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm(['image_path' => [UploadedFile::fake()->image('nouveau.png')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $event->refresh();
        Storage::disk('public')->assertMissing('events/flyers/ancien.png');
        Storage::disk('public')->assertExists($event->image_path);
    }

    public function test_event_date_defaults_to_first_scheduled_day(): void
    {
        $event = Event::create([
            'title' => 'Congrès',
            'schedule_items' => [
                ['label' => 'Jour 2', 'date' => '2026-11-13'],
                ['label' => 'Jour 1', 'date' => '2026-11-12'],
            ],
        ]);

        $this->assertSame('2026-11-12', $event->event_date->toDateString());
    }

    public function test_public_agenda_links_to_non_image_event_flyer(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/flyers/programme.pdf', 'programme pdf');

        Event::create([
            'title' => 'Evenement PDF',
            'event_date' => now()->addWeek()->toDateString(),
            'image_path' => 'events/flyers/programme.pdf',
            'is_published' => true,
        ]);

        $event = Event::firstWhere('title', 'Evenement PDF');

        $this->get(route('agenda'))->assertOk()->assertSee('Evenement PDF')->assertSee(route('agenda.show', $event));

        // Le flyer PDF est proposé sur la fiche de l'événement.
        $this->get(route('agenda.show', $event))
            ->assertOk()
            ->assertSee('Ouvrir le flyer (PDF)')
            ->assertSee('/storage/events/flyers/programme.pdf', false);
    }

    public function test_admin_can_export_registrations_as_csv(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $event = Event::create(['title' => 'Atelier CSV', 'event_date' => now()->addWeek(), 'is_published' => true]);
        $event->registrations()->create(['name' => 'Jean Export', 'email' => 'jean@example.com']);

        Livewire::test(ManageEventRegistrations::class)
            ->callAction('export')
            ->assertFileDownloaded('inscriptions-evenements.csv');
    }

    public function test_admin_can_toggle_professional_access_and_verification(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $professional = User::factory()->unverified()->create(['is_member' => true, 'is_health_professional' => true]);

        Livewire::test(ManageProfessionals::class)
            ->assertCanSeeTableRecords([$professional])
            ->callAction(TestAction::make('toggleVerification')->table($professional))
            ->call('updateTableColumnState', 'is_member', (string) $professional->getKey(), false);

        $professional->refresh();
        $this->assertNotNull($professional->email_verified_at);
        $this->assertFalse($professional->is_member);
    }

    public function test_admins_cannot_be_deleted_from_professional_accounts(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $professional = User::factory()->create(['is_member' => true, 'is_health_professional' => true]);
        $professionalAdmin = User::factory()->create(['is_admin' => true, 'is_member' => true]);

        Livewire::test(ManageProfessionals::class)
            ->assertActionHidden(TestAction::make('delete')->table($professionalAdmin))
            ->callAction(TestAction::make('delete')->table($professional));

        $this->assertModelMissing($professional);
        $this->assertModelExists($professionalAdmin);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->assertActionHidden(TestAction::make('delete')->table($admin));
    }

    public function test_user_password_is_only_changed_when_filled(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $user = User::factory()->create(['password' => 'mot-de-passe-initial']);
        $hash = $user->password;

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($user), data: ['name' => 'Nouveau nom', 'password' => ''])
            ->assertHasNoActionErrors();

        $user->refresh();
        $this->assertSame('Nouveau nom', $user->name);
        $this->assertSame($hash, $user->password);
    }
}
