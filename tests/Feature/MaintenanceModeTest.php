<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visitors_see_maintenance_page_when_enabled(): void
    {
        SiteSetting::setValue('maintenance_enabled', '1');
        SiteSetting::setValue('maintenance_message', 'Maintenance en cours.');

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Maintenance en cours.')
            ->assertDontSee('Acces equipe')
            ->assertDontSee(route('pro'));
    }

    public function test_professional_area_is_not_accessible_during_maintenance(): void
    {
        SiteSetting::setValue('maintenance_enabled', '1');
        SiteSetting::setValue('maintenance_message', 'Maintenance en cours.');

        $this->get(route('pro'))
            ->assertStatus(503)
            ->assertSee('Maintenance en cours.')
            ->assertDontSee('Acces equipe');
    }

    public function test_admin_login_stays_accessible_during_maintenance(): void
    {
        SiteSetting::setValue('maintenance_enabled', '1');
        SiteSetting::setValue('maintenance_message', 'Maintenance en cours.');

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Connexion admin');
    }

    public function test_admin_can_access_site_when_maintenance_is_enabled(): void
    {
        SiteSetting::setValue('maintenance_enabled', '1');
        SiteSetting::setValue('maintenance_message', 'Maintenance en cours.');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk();
    }
}
