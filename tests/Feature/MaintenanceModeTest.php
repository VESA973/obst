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
            ->assertSee('Maintenance en cours.');
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
