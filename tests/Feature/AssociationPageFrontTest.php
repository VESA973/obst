<?php

namespace Tests\Feature;

use App\Models\AssociationPageImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Page publique « L'association » (/qui-sommes-nous).
 */
class AssociationPageFrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_texts_are_kept(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Qui sommes-nous')
            ->assertSee('Agir durablement pour la santé sur le territoire guyanais')
            ->assertSee('Depuis plus de 15 ans, nous développons des actions de formation')
            ->assertSee('Former, informer, sensibiliser et faire coopérer')
            ->assertSee('Former les professionnels')
            ->assertSee('Améliorer l\'organisation des soins')
            ->assertSee('Faire de la Guyane un territoire d\'excellence', false)
            ->assertSee(route('actions'))
            ->assertSee(route('join'))
            ->assertSee(route('contact'));
    }

    public function test_page_works_without_any_image(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('assoc-img--placeholder', false)
            // Pas de galerie tant qu'aucune photo visible n'existe.
            ->assertDontSee('assoc-gallery', false)
            // Bannière : image d'en-tête de « Pages du site » en attendant une photo dédiée.
            ->assertSee('images/page-headers/about.webp', false);
    }

    public function test_slot_images_and_visible_gallery_photos_are_displayed(): void
    {
        Storage::fake('public');
        foreach (['association/hero.jpg', 'association/galerie/visible.jpg', 'association/galerie/masquee.jpg'] as $path) {
            Storage::disk('public')->putFileAs(dirname($path), UploadedFile::fake()->image(basename($path), 1600, 900), basename($path));
        }
        AssociationPageImage::create(['slot' => 'hero', 'path' => 'association/hero.jpg', 'alt' => 'Soignantes à Saint-Laurent']);
        AssociationPageImage::create(['slot' => 'gallery', 'path' => 'association/galerie/visible.jpg', 'alt' => 'Stand de prévention', 'caption' => 'Marché de Cayenne']);
        AssociationPageImage::create(['slot' => 'gallery', 'path' => 'association/galerie/masquee.jpg', 'alt' => 'Photo masquée', 'is_visible' => false]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('alt="Soignantes à Saint-Laurent"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('thumbs/1200/association/hero.webp 1200w', false)
            ->assertSee('property="og:image" content="'.Storage::disk('public')->url('association/hero.jpg').'"', false)
            ->assertSee('class="assoc-gallery-link glightbox"', false)
            ->assertSee('data-description="Marché de Cayenne"', false)
            ->assertSee('Stand de prévention')
            ->assertDontSee('Photo masquée');
    }

    public function test_seo_stats_and_timeline(): void
    {
        $this->get(route('about'))
            ->assertSee('"@type":"NGO"', false)
            ->assertSee('<link rel="canonical" href="'.route('about').'">', false)
            ->assertSee('data-count-to="15"', false)
            ->assertSee('data-count-to="11"', false)
            // Frise masquée tant qu'aucune étape n'est renseignée.
            ->assertDontSee('assoc-timeline-step', false);

        config(['association.timeline' => [['year' => '2010', 'title' => 'Première édition', 'text' => 'Texte validé.']]]);

        $this->get(route('about'))
            ->assertSee('assoc-timeline-step', false)
            ->assertSee('Première édition');
    }

    public function test_home_stats_come_from_the_shared_config(): void
    {
        config(['association.stats' => [['value' => 42, 'suffix' => '', 'label' => 'bénévoles', 'on_home' => true]]]);

        $this->get(route('home'))->assertSee('42')->assertSee('bénévoles');
    }
}
