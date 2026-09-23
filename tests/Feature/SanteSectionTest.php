<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Section publique « Santé de la femme » (design blog santé).
 */
class SanteSectionTest extends TestCase
{
    use RefreshDatabase;

    private ArticleCategory $grossesse;

    private ArticleCategory $cycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grossesse = ArticleCategory::factory()->publicSection()->create(['name' => 'Grossesse', 'slug' => 'grossesse', 'description' => 'Suivi et examens.', 'position' => 1]);
        $this->cycle = ArticleCategory::factory()->publicSection()->create(['name' => 'Cycle menstruel', 'slug' => 'cycle-menstruel', 'position' => 2]);
    }

    public function test_home_uses_the_same_design_as_the_news_section(): void
    {
        $featured = Article::factory()->for($this->grossesse, 'category')->create(['title' => 'Article vedette', 'published_at' => now()->subMinute()]);
        Article::factory()->count(7)->for($this->cycle, 'category')->create(['published_at' => now()->subDay()]);

        $this->get(route('public'))
            ->assertOk()
            // Même gabarit que les Actualités : conteneur, barre de section, pied de page éditorial.
            ->assertSee('data-news-root', false)
            ->assertSee('<span class="font-serif text-xl font-bold tracking-tight">Santé de la femme</span>', false)
            ->assertSeeInOrder(['Grossesse', 'Cycle menstruel'])
            ->assertSee('action="'.route('public').'"', false)
            ->assertSee('Article vedette')
            ->assertSee($featured->publicUrl())
            ->assertSee('Derniers articles')
            ->assertSee(route('public.category', $this->grossesse))
            ->assertSee(route('newsletter.subscribe'))
            // Pas de bandeau « Dernière minute » ni de flux RSS pour cette section.
            ->assertDontSee('Dernière minute')
            ->assertDontSee('application/rss+xml', false);
    }

    public function test_home_ignores_news_drafts_and_inactive_rubriques(): void
    {
        Article::factory()->for(ArticleCategory::factory(), 'category')->create(['title' => 'Actualite hors section']);
        Article::factory()->draft()->for($this->grossesse, 'category')->create(['title' => 'Brouillon sante']);
        $inactive = ArticleCategory::factory()->publicSection()->inactive()->create(['name' => 'Rubrique masquee']);
        Article::factory()->for($inactive, 'category')->create(['title' => 'Article masque']);

        $this->get(route('public'))
            ->assertOk()
            ->assertDontSee('Actualite hors section')
            ->assertDontSee('Brouillon sante')
            ->assertDontSee('Rubrique masquee')
            ->assertDontSee('Article masque');
    }

    public function test_search_is_limited_to_the_health_section(): void
    {
        Article::factory()->for($this->grossesse, 'category')->create(['title' => 'Nausées et grossesse']);
        Article::factory()->for(ArticleCategory::factory(), 'category')->create(['title' => 'Nausées dans les actualités']);

        $this->get(route('public', ['q' => 'nausées']))
            ->assertOk()
            ->assertSee('1 résultat')
            ->assertSee('<mark>Nausées</mark> et grossesse', false)
            ->assertDontSee('dans les actualités')
            ->assertSee('noindex', false);

        $this->get(route('public', ['q' => 'introuvable']))
            ->assertOk()
            ->assertSee('Aucun article ne correspond');

        // Les rubriques proposées quand rien n'est trouvé sont celles de la section.
        $this->get(route('public', ['q' => 'introuvable']))
            ->assertSee(route('public.category', $this->grossesse))
            ->assertDontSee('/actualites/', false);
    }

    public function test_rubrique_page_has_banner_and_load_more(): void
    {
        config(['news.ticker' => false]);
        Article::factory()->count(14)->for($this->grossesse, 'category')->create();

        $this->get(route('public.category', $this->grossesse))
            ->assertOk()
            ->assertSeeInOrder(['Accueil', 'Santé de la femme', 'Grossesse'])
            ->assertSee('Suivi et examens.')
            ->assertSee('14 articles')
            ->assertSee('Charger plus')
            ->assertSee('<link rel="next"', false);

        $this->get(route('public.category', ['category' => $this->grossesse, 'page' => 2, 'fragment' => 1]))
            ->assertOk()
            ->assertHeader('X-Next-Page', '')
            ->assertDontSee('<html', false);
    }

    public function test_article_page_has_toc_disclaimer_share_progress_and_related(): void
    {
        $author = User::factory()->create(['name' => 'Dre Martin']);
        $article = Article::factory()->for($this->grossesse, 'category')->for($author, 'author')->create([
            'title' => 'Les examens du premier trimestre',
            'excerpt' => 'Un chapeau rassurant.',
            'body' => '<h2>Première partie</h2><p>Texte.</p><h3>Détail</h3><p>Texte.</p><h2>Seconde partie</h2><p>Texte.</p>',
        ]);
        $related = Article::factory()->for($this->grossesse, 'category')->create(['title' => 'Article voisin']);

        $this->get($article->publicUrl())
            ->assertOk()
            ->assertSeeInOrder(['Accueil', 'Santé de la femme', 'Grossesse', 'Les examens du premier trimestre'])
            ->assertSee('Un chapeau rassurant.')
            ->assertSee('Dre Martin')
            ->assertSee('min de lecture')
            ->assertSee('data-article-toc', false)
            ->assertSee('<h2>Première partie</h2>', false)
            ->assertSee('Ces informations ne remplacent pas l\'avis d\'un professionnel de santé.', false)
            ->assertSee('data-reading-progress', false)
            ->assertSee('data-copy-link', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('À lire aussi')
            ->assertSee($related->publicUrl());
    }

    public function test_pages_do_not_run_one_query_per_article(): void
    {
        $rubriqueUrl = route('public.category', $this->grossesse);
        Article::factory()->for($this->grossesse, 'category')->create();
        Article::factory()->for($this->cycle, 'category')->create();

        $count = function (string $url): int {
            // Accueil mis en cache : on mesure le calcul complet.
            Cache::flush();
            DB::flushQueryLog();
            $this->get($url)->assertOk();

            return count(DB::getQueryLog());
        };

        // Échauffement : la première requête remplit les caches (réglages du site).
        $this->get(route('public'));
        $this->get($rubriqueUrl);

        DB::enableQueryLog();
        $homeFew = $count(route('public'));
        $rubriqueFew = $count($rubriqueUrl);

        // Mêmes rubriques, beaucoup plus d'articles : le nombre de requêtes ne doit pas grimper.
        Article::factory()->count(6)->for($this->grossesse, 'category')->create();
        Article::factory()->count(6)->for($this->cycle, 'category')->create();

        $this->assertSame($homeFew, $count(route('public')));
        $this->assertSame($rubriqueFew, $count($rubriqueUrl));
    }

    public function test_news_articles_do_not_show_the_health_extras(): void
    {
        $news = Article::factory()->for(ArticleCategory::factory(), 'category')->create([
            'body' => '<h2>Un</h2><p>Texte.</p><h2>Deux</h2><p>Texte.</p>',
        ]);

        $this->get($news->publicUrl())
            ->assertOk()
            ->assertDontSee('data-article-toc', false)
            ->assertDontSee('ne remplacent pas');
    }
}
