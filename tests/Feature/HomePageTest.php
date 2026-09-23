<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Page d'accueil (refonte « site santé »).
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_keeps_the_photo_optimised_and_preloaded(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('rel="preload" as="image" type="image/webp"', false)
            ->assertSee('images/guyane-hero-800.webp 800w', false)
            ->assertSee('images/guyane-hero.png', false)
            ->assertSee('Informer. Former. Innover. Agir.')
            ->assertSee(route('join'))
            ->assertSee(route('contact'));

        foreach ([800, 1200, 1808] as $width) {
            $this->assertFileExists(public_path("images/guyane-hero-{$width}.webp"));
        }
    }

    public function test_quick_access_links_to_existing_pages_and_emergency_numbers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Accès rapides"', false)
            ->assertSee(route('public'))
            ->assertSee(route('news'))
            ->assertSee(route('agenda'))
            ->assertSee(route('pro'))
            ->assertSee('href="tel:15"', false)
            ->assertSee('href="tel:112"', false);
    }

    public function test_greeting_only_shows_configured_translations(): void
    {
        config(['home.greetings' => [
            ['lang' => 'fr', 'label' => 'Français', 'text' => 'Bienvenue'],
            ['lang' => 'gcr', 'label' => 'Créole guyanais', 'text' => 'Texte validé'],
        ]]);

        $this->get(route('home'))
            ->assertSee('<span lang="fr" title="Français">Bienvenue</span>', false)
            ->assertSee('<span lang="gcr" title="Créole guyanais">Texte validé</span>', false);
    }

    public function test_carousels_show_news_health_articles_and_events(): void
    {
        $news = Article::factory()->for(ArticleCategory::factory(), 'category')->create(['title' => 'Actualite du jour']);
        $rubrique = ArticleCategory::factory()->publicSection()->create(['name' => 'Grossesse']);
        $health = Article::factory()->for($rubrique, 'category')->create(['title' => 'Fiche grossesse']);
        Event::create(['title' => 'Colloque de Cayenne', 'event_date' => now()->addWeek(), 'is_published' => true, 'location' => 'Cayenne']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-autoplay', false)
            ->assertSee('Actualite du jour')
            ->assertSee($news->publicUrl())
            ->assertSee('Santé des femmes en Guyane')
            ->assertSee('Fiche grossesse')
            ->assertSee($health->publicUrl())
            ->assertSee(route('public.category', $rubrique))
            ->assertSee('Colloque de Cayenne')
            ->assertSee('Cayenne');
    }

    public function test_existing_fallback_content_is_kept_when_empty(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Parler santé de la femme en Guyane')
            ->assertSee('Les Jeudis M')
            ->assertSee('Qui sommes-nous ?')
            ->assertSee('actions phares')
            ->assertSee('Agir avec l\'association', false)
            // Pas de bloc santé sans fiche publiée.
            ->assertDontSee('Santé des femmes en Guyane');
    }

    public function test_home_assets_and_emergency_button_are_limited_to_the_home_page(): void
    {
        // Vite référence les fichiers compilés : build/assets/home-….js et home-….css.
        $this->get(route('home'))->assertSee('home-header-emergency', false)->assertSee('build/assets/home-', false);

        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee('home-header-emergency', false)
            ->assertDontSee('build/assets/home-', false);
    }
}
