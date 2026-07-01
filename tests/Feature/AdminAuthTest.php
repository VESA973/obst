<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\ResourceFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_entry_shows_login_form_for_guest(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Connexion admin')
            ->assertDontSee('Espace pro')
            ->assertDontSee("L'association");
    }

    public function test_guest_admin_module_redirects_to_admin_login(): void
    {
        $this->get(route('admin.configuration'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_admin_bootstrap_redirects_to_admin_login(): void
    {
        $this->get(route('admin.bootstrap'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_admin_logout_redirects_to_admin_login(): void
    {
        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_to_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
            ...$this->validAntiBotPayload('admin_login'),
        ])
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_requires_valid_anti_bot_challenge(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
            'website' => '',
            'form_started_at' => now()->subSeconds(5)->timestamp,
            'antibot_token' => 'missing-token',
            'antibot_answer' => '4',
        ])
            ->assertSessionHasErrors('antibot_answer');

        $this->assertGuest();
    }

    public function test_admin_dashboard_uses_dedicated_admin_layout(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Modules du site')
            ->assertDontSee('Espace pro')
            ->assertDontSee("L'association");
    }

    public function test_admin_google_redirect_uri_uses_app_url(): void
    {
        config(['app.url' => 'https://laquinzaineobstetricale.fr']);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.configuration'))
            ->assertOk()
            ->assertSee('https://laquinzaineobstetricale.fr/professionnels/google/retour', false);
    }

    public function test_public_navigation_uses_association_submenu_and_hides_search(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('nav-submenu', false)
            ->assertSee('Nos actions')
            ->assertSee('Santé de la femme')
            ->assertDontSee('Recherche')
            ->assertDontSee('Sante des femmes');
    }

    public function test_admin_can_choose_how_many_events_are_listed(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (range(1, 12) as $index) {
            Event::create([
                'title' => sprintf('Evenement pagination %02d', $index),
                'event_date' => now()->addDays($index)->toDateString(),
                'is_published' => true,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('12 événement(s)')
            ->assertSee('Evenement pagination 01')
            ->assertSee('Evenement pagination 10')
            ->assertDontSee('Evenement pagination 11');

        $this->actingAs($admin)
            ->get(route('admin.events.index', ['events_per_page' => 20]))
            ->assertOk()
            ->assertSee('Evenement pagination 11');
    }

    public function test_admin_event_list_displays_description_when_available(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Event::create([
            'title' => 'Atelier perinatalite',
            'event_date' => now()->addWeek()->toDateString(),
            'location' => 'Cayenne',
            'description' => 'Description visible dans la ligne de l evenement.',
            'is_published' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Atelier perinatalite')
            ->assertSee('Description visible dans la ligne de l evenement.');
    }

    public function test_admin_can_replace_event_main_flyer(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        Storage::disk('public')->put('events/ancien.jpg', 'ancien flyer');
        $event = Event::create([
            'title' => 'Evenement avec flyer',
            'event_date' => now()->addWeek()->toDateString(),
            'image_path' => 'events/ancien.jpg',
            'is_published' => true,
        ]);

        $oldPath = $event->image_path;

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), [
                'title' => 'Evenement avec nouveau flyer',
                'event_date' => now()->addWeek()->toDateString(),
                'image' => UploadedFile::fake()->create('nouveau-flyer.pdf', 32, 'application/pdf'),
                'is_published' => '1',
            ])
            ->assertRedirect();

        $event->refresh();

        $this->assertNotSame($oldPath, $event->image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($event->image_path);
        $this->assertStringEndsWith('.pdf', $event->image_path);
    }

    public function test_admin_can_use_png_as_event_main_flyer(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Evenement flyer PNG',
                'event_date' => now()->addWeek()->toDateString(),
                'image' => new UploadedFile(public_path('images/quinzaine-hero.png'), 'flyer-evenement.png', 'image/png', null, true),
                'is_published' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $event = Event::where('title', 'Evenement flyer PNG')->firstOrFail();

        $this->assertTrue($event->flyer_is_image);
        $this->assertSame('PNG', $event->flyer_extension);
        Storage::disk('public')->assertExists($event->image_path);

        $this->get(route('agenda'))
            ->assertOk()
            ->assertSee('event-flyer-frame')
            ->assertSee('/storage/events/flyers/', false);
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

        $this->get(route('agenda'))
            ->assertOk()
            ->assertSee('Evenement PDF')
            ->assertSee('PDF')
            ->assertSee('Ouvrir le flyer')
            ->assertSee('/storage/events/flyers/programme.pdf', false);
    }

    public function test_admin_files_are_displayed_with_clear_metadata(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        ResourceFile::create([
            'title' => 'Programme annuel',
            'audience' => 'pro',
            'category' => 'Programme',
            'description' => 'Document de presentation pour les equipes.',
            'path' => 'resources/programme.pdf',
            'original_name' => 'programme-2026.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.files.index'))
            ->assertOk()
            ->assertSee('Programme annuel')
            ->assertSee('Professionnels')
            ->assertSee('Programme')
            ->assertSee('programme-2026.pdf')
            ->assertSee('Document de presentation pour les equipes.');
    }

    public function test_admin_can_configure_public_page_header_and_menu_label(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.pages.update'), [
                'pages' => [
                    'about' => [
                        'menu_label' => 'Qui sommes nous',
                        'eyebrow' => 'Association',
                        'title' => 'Notre equipe et notre histoire',
                        'description' => 'Texte configure depuis le backend.',
                        'title_size' => 'large',
                        'show_in_menu' => '1',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Qui sommes nous')
            ->assertSee('Association')
            ->assertSee('Notre equipe et notre histoire')
            ->assertSee('Texte configure depuis le backend.')
            ->assertSee('title-large');
    }

    public function test_public_pages_have_default_example_header_images(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('has-hero-image')
            ->assertSee('/images/page-headers/quinzaine-page-example.png', false);
    }

    public function test_admin_can_upload_png_page_header(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.pages.update'), [
                'pages' => [
                    'about' => [
                        'menu_label' => 'Qui sommes nous',
                        'eyebrow' => 'Association',
                        'title' => 'Notre equipe',
                        'description' => 'Entete avec image PNG.',
                        'title_size' => 'normal',
                        'show_in_menu' => '1',
                    ],
                ],
                'hero_images' => [
                    'about' => new UploadedFile(public_path('images/quinzaine-hero.png'), 'entete.png', 'image/png', null, true),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('page_settings', [
            'page_key' => 'about',
        ]);
    }

    public function test_admin_can_disable_professional_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $professional = User::factory()->create([
            'is_member' => true,
            'is_health_professional' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.professionals.update', $professional), [
                'is_health_professional' => '1',
                'email_verified' => '1',
            ])
            ->assertRedirect();

        $professional->refresh();

        $this->assertFalse($professional->is_member);
        $this->assertTrue($professional->is_health_professional);
        $this->assertNotNull($professional->email_verified_at);
    }

    public function test_admin_can_delete_professional_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $professional = User::factory()->create([
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.professionals.destroy', $professional))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $professional->id,
        ]);
    }

    public function test_admin_cannot_delete_admin_from_professional_accounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $professionalAdmin = User::factory()->create([
            'is_admin' => true,
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.professionals.destroy', $professionalAdmin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $professionalAdmin->id,
        ]);
    }

    public function test_non_admin_cannot_login_to_admin(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'is_admin' => false,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'member@example.com',
            'password' => 'password',
            ...$this->validAntiBotPayload('admin_login'),
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
