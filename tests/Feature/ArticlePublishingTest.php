<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticlePublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_article_is_visible_on_news_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Nouvelle actualite de test',
                'category' => 'Article',
                'excerpt' => 'Resume visible sur la page actualites.',
            ])
            ->assertRedirect();

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Nouvelle actualite de test')
            ->assertSee('Resume visible sur la page actualites.')
            ->assertSee(route('articles.show', 'nouvelle-actualite-de-test'));
    }

    public function test_article_has_a_public_detail_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article detail visible',
                'excerpt' => 'Accroche de l article.',
                'body' => 'Contenu complet de l actualite.',
            ]);

        $this->get(route('articles.show', 'article-detail-visible'))
            ->assertOk()
            ->assertSee('Article detail visible')
            ->assertSee('Contenu complet de l actualite.');
    }

    public function test_article_detail_page_displays_uploaded_photos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article avec photos',
                'excerpt' => 'Actualite illustree.',
                'photos' => [
                    UploadedFile::fake()->create('atelier.jpg', 10, 'image/jpeg'),
                ],
            ]);

        $this->get(route('articles.show', 'article-avec-photos'))
            ->assertOk()
            ->assertSee('articles/photos', false);
    }

    public function test_article_detail_page_displays_main_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article image principale',
                'excerpt' => 'Article avec image.',
                'image' => UploadedFile::fake()->create('principale.jpg', 10, 'image/jpeg'),
            ]);

        $this->get(route('articles.show', 'article-image-principale'))
            ->assertOk()
            ->assertSee('article-main-image')
            ->assertSee('articles/', false);
    }

    public function test_article_can_be_linked_to_a_managed_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.article-categories.store'), [
                'name' => 'Prevention',
            ])
            ->assertRedirect();

        $category = \App\Models\ArticleCategory::where('name', 'Prevention')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article categorie',
                'article_category_id' => $category->id,
                'excerpt' => 'Article lie a une categorie.',
            ])
            ->assertRedirect();

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Prevention')
            ->assertSee('Article categorie');

        $this->get(route('articles.show', 'article-categorie'))
            ->assertOk()
            ->assertSee('Prevention');
    }

    public function test_admin_can_choose_how_many_articles_are_listed(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (range(1, 12) as $index) {
            Article::create([
                'title' => sprintf('Article pagination %02d', $index),
                'slug' => sprintf('article-pagination-%02d', $index),
                'published_at' => now()->addMinutes($index),
                'is_published' => true,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('12 actualité(s)')
            ->assertSee('Article pagination 12')
            ->assertSee('Article pagination 03')
            ->assertDontSee('Article pagination 02');

        $this->actingAs($admin)
            ->get(route('admin.articles.index', ['articles_per_page' => 20]))
            ->assertOk()
            ->assertSee('Article pagination 02');
    }
}
