<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleAsset;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Affichage des articles sur le site public.
 */
class ArticlePublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_is_visible_on_news_page(): void
    {
        $article = Article::factory()->create([
            'title' => 'Nouvelle actualite de test',
            'excerpt' => 'Resume visible sur la page actualites.',
        ]);

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Nouvelle actualite de test')
            ->assertSee('Resume visible sur la page actualites.')
            ->assertSee($article->publicUrl());
    }

    public function test_article_detail_page_renders_sanitized_html_and_seo_tags(): void
    {
        Article::factory()->create([
            'title' => 'Article detail visible',
            'slug' => 'article-detail-visible',
            'body' => '<h2>Intertitre</h2><p>Contenu <strong>complet</strong>.</p><script>alert(1)</script>',
            'meta_title' => 'Titre SEO dedie',
            'meta_description' => 'Description SEO dediee.',
        ]);

        $this->get($this->newsUrl('article-detail-visible'))
            ->assertOk()
            ->assertSee('<h2>Intertitre</h2>', false)
            ->assertSee('<strong>complet</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('<title>Titre SEO dedie | La Quinzaine Obstétricale</title>', false)
            ->assertSee('content="Description SEO dediee."', false);
    }

    public function test_article_detail_page_displays_responsive_main_image_and_photos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->putFileAs('articles', UploadedFile::fake()->image('principale.jpg', 1600, 900), 'principale.jpg');

        $article = Article::factory()->create(['slug' => 'article-illustre', 'image_path' => 'articles/principale.jpg']);
        ArticleAsset::create(['article_id' => $article->id, 'type' => 'photo', 'path' => 'articles/photos/atelier.jpg']);

        $this->get($this->newsUrl('article-illustre'))
            ->assertOk()
            ->assertSee('thumbs/1200/articles/principale.webp 1200w', false)
            ->assertSee('articles/photos/atelier.jpg', false);

        Storage::disk('public')->assertExists('thumbs/400/articles/principale.webp');
    }

    public function test_draft_and_scheduled_articles_are_hidden(): void
    {
        Article::factory()->draft()->create(['title' => 'Brouillon cache', 'slug' => 'brouillon-cache']);
        Article::factory()->scheduled()->create(['title' => 'Programme cache', 'slug' => 'programme-cache']);

        $this->get(route('news'))
            ->assertOk()
            ->assertDontSee('Brouillon cache')
            ->assertDontSee('Programme cache');

        $this->get($this->newsUrl('brouillon-cache'))->assertNotFound();
        $this->get($this->newsUrl('programme-cache'))->assertNotFound();
    }

    public function test_scheduled_article_appears_once_its_date_is_reached(): void
    {
        Article::factory()->scheduled()->create(['title' => 'Bientot en ligne', 'slug' => 'bientot-en-ligne']);

        $this->travel(8)->days();

        $this->get('/actualites/'.Article::firstWhere('slug', 'bientot-en-ligne')->category->slug.'/bientot-en-ligne')->assertOk();
    }

    public function test_articles_of_an_inactive_category_are_hidden(): void
    {
        $category = ArticleCategory::factory()->inactive()->create();
        Article::factory()->for($category, 'category')->create(['title' => 'Categorie masquee', 'slug' => 'categorie-masquee']);

        $this->get(route('news'))->assertOk()->assertDontSee('Categorie masquee');
        $this->get($this->newsUrl('categorie-masquee'))->assertNotFound();
    }

    public function test_article_shows_its_category(): void
    {
        $category = ArticleCategory::factory()->create(['name' => 'Prevention']);
        Article::factory()->for($category, 'category')->create(['title' => 'Article categorie', 'slug' => 'article-categorie']);

        $this->get(route('news'))->assertOk()->assertSee('Prevention')->assertSee('Article categorie');
        $this->get($this->newsUrl('article-categorie'))->assertOk()->assertSee('Prevention');
    }

    public function test_public_health_category_lists_its_articles(): void
    {
        $category = ArticleCategory::factory()->publicSection()->create([
            'name' => 'Endométriose',
            'title' => 'Comprendre l’endométriose',
            'image_path' => 'article-categories/endometriose.png',
        ]);
        Article::factory()->for($category, 'category')->create(['title' => 'Repères utiles', 'slug' => 'reperes-utiles']);

        $this->get(route('public'))
            ->assertOk()
            ->assertSee('Endométriose')
            ->assertSee('Comprendre l’endométriose')
            ->assertSee(route('public.category', $category));

        $this->get(route('public.category', $category))
            ->assertOk()
            ->assertSee('Repères utiles')
            ->assertSee(route('public.article', [$category, 'reperes-utiles']));

        $this->get(route('public.article', [$category, 'reperes-utiles']))
            ->assertOk()
            ->assertSeeInOrder(['Santé de la femme', 'Endométriose', 'Repères utiles']);

        // Les articles « Santé de la femme » ne sont pas publiés dans les actualités.
        $this->get('/actualites/'.$category->slug.'/reperes-utiles')->assertNotFound();
    }

    public function test_inactive_public_category_is_not_reachable(): void
    {
        $category = ArticleCategory::factory()->publicSection()->inactive()->create(['name' => 'Menopause']);

        $this->get(route('public'))->assertOk()->assertDontSee('Menopause');
        $this->get(route('public.category', $category))->assertNotFound();
    }

    /**
     * Adresse publique d'un article d'actualité (catégorie + slug).
     */
    private function newsUrl(string $slug): string
    {
        $article = Article::with('category')->firstWhere('slug', $slug);

        return '/actualites/'.$article->category->slug.'/'.$slug;
    }
}
