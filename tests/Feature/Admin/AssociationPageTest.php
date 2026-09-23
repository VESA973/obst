<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\AssociationPage;
use App\Models\AssociationPageImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Photos de la page « L'association » dans Filament.
 */
class AssociationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_set_a_slot_image_with_alt_text(): void
    {
        Livewire::test(AssociationPage::class)
            ->fillForm([
                'slots.mission.path' => [UploadedFile::fake()->image('mission.jpg', 1200, 900)],
                'slots.mission.alt' => 'Atelier de prévention à Cayenne',
                'slots.mission.caption' => 'Atelier 2025',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $image = AssociationPageImage::firstWhere('slot', 'mission');

        $this->assertSame('Atelier de prévention à Cayenne', $image->alt);
        $this->assertSame('Atelier 2025', $image->caption);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_alt_text_is_required_when_an_image_is_chosen(): void
    {
        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.hero.path' => [UploadedFile::fake()->image('hero.jpg', 1600, 900)], 'slots.hero.alt' => ''])
            ->call('save')
            ->assertHasFormErrors(['slots.hero.alt' => 'required']);

        $this->assertDatabaseCount('association_page_images', 0);
    }

    public function test_slot_images_must_match_the_crop_ratio(): void
    {
        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.hero.path' => [UploadedFile::fake()->image('carre.jpg', 800, 800)], 'slots.hero.alt' => 'Carré'])
            ->call('save')
            ->assertHasFormErrors(['slots.hero.path']);
    }

    public function test_only_jpg_png_and_webp_are_accepted(): void
    {
        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.hero.path' => [UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf')], 'slots.hero.alt' => 'Plan'])
            ->call('save')
            ->assertHasFormErrors(['slots.hero.path']);
    }

    public function test_replacing_or_removing_an_image_deletes_the_old_file(): void
    {
        Storage::disk('public')->put('association/ancienne.jpg', 'x');
        AssociationPageImage::create(['slot' => 'history', 'path' => 'association/ancienne.jpg', 'alt' => 'Ancienne']);

        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.history.path' => [UploadedFile::fake()->image('nouvelle.jpg', 1200, 900)], 'slots.history.alt' => 'Nouvelle'])
            ->call('save')
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertMissing('association/ancienne.jpg');
        $newPath = AssociationPageImage::firstWhere('slot', 'history')->path;

        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.history.path' => null, 'slots.history.alt' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull(AssociationPageImage::firstWhere('slot', 'history'));
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_gallery_is_synchronised_in_order_with_visibility(): void
    {
        Storage::disk('public')->put('association/galerie/a.jpg', 'x');
        Storage::disk('public')->put('association/galerie/b.jpg', 'x');
        $first = AssociationPageImage::create(['slot' => 'gallery', 'path' => 'association/galerie/a.jpg', 'alt' => 'A', 'position' => 0]);
        $removed = AssociationPageImage::create(['slot' => 'gallery', 'path' => 'association/galerie/b.jpg', 'alt' => 'B', 'position' => 1]);

        // Nouvel ordre : une nouvelle photo masquée d'abord, puis A ; B est retirée.
        Livewire::test(AssociationPage::class)
            ->fillForm(['gallery' => [
                ['id' => null, 'path' => [UploadedFile::fake()->image('c.jpg')], 'alt' => 'C', 'caption' => null, 'is_visible' => false],
                ['id' => $first->id, 'path' => ['association/galerie/a.jpg'], 'alt' => 'A modifiée', 'caption' => 'Légende A', 'is_visible' => true],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $gallery = AssociationPageImage::query()->gallery()->get();

        $this->assertSame(['C', 'A modifiée'], $gallery->pluck('alt')->all());
        $this->assertSame([false, true], $gallery->pluck('is_visible')->all());
        $this->assertModelMissing($removed);
        Storage::disk('public')->assertMissing('association/galerie/b.jpg');
    }

    public function test_page_cache_is_refreshed_on_save(): void
    {
        $this->assertTrue(AssociationPageImage::forPage()['slots']->isEmpty());
        $this->assertTrue(Cache::has(AssociationPageImage::CACHE_KEY));

        Livewire::test(AssociationPage::class)
            ->fillForm(['slots.actions.path' => [UploadedFile::fake()->image('actions.jpg', 1200, 900)], 'slots.actions.alt' => 'Actions'])
            ->call('save');

        $this->assertSame('Actions', AssociationPageImage::forPage()['slots']->get('actions')->alt);
    }
}
