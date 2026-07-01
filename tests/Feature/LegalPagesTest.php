<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_available(): void
    {
        $this->get(route('legal.mentions'))
            ->assertOk()
            ->assertSee('Mentions légales');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Politique de confidentialité');

        $this->get(route('legal.cookies'))
            ->assertOk()
            ->assertSee('Politique de cookies');

        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Conditions Générales d’Utilisation');
    }

    public function test_footer_contains_legal_links_and_cookie_banner(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal.mentions'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.cookies'), false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee('Préférences cookies')
            ->assertSee('data-cookie-banner', false)
            ->assertSee('data-cookie-choice="accepted"', false)
            ->assertSee('data-cookie-choice="refused"', false);
    }
}
