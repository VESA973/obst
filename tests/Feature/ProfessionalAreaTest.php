<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_authentication_gate_on_professional_page(): void
    {
        $this->get('/espace-pro')
            ->assertOk()
            ->assertSee('Accedez aux contenus reserves')
            ->assertDontSee('Consulter les dossiers');
    }

    public function test_authenticated_professional_sees_private_content(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/espace-pro')
            ->assertOk()
            ->assertSee('Consulter les dossiers')
            ->assertSee('Reserve pro')
            ->assertDontSee('Creer un compte pro');
    }
}
