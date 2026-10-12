<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Support\NewsBlocks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rubrique publique « Actualités ».
 */
class NewsTest extends TestCase
{
    use RefreshDatabase;

    private ArticleCategory $tribunes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tribunes = ArticleCategory::factory()->create(['name' => 'Tribunes', 'slug' => 'tribunes', 'description' => 'Regards d\'experts.']);
    }

    public function test_home_shows_featured_latest_and_category_sections(): void
    {
        $featured = Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Article a la une', 'published_at' => now()->subMinute()]);
        Article::factory()->count(6)->for($this->tribunes, 'category')->create(['published_at' => now()->subDays(2)]);

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Article a la une')
            ->assertSee($featured->publicUrl())
            ->assertSee('Dernières actualités')
            ->assertSee(route('news.category', 'tribunes'))
            ->assertSee('<link rel="canonical" href="'.route('news').'">', false)
            ->assertSee('application/rss+xml', false);
    }

    public function test_home_hides_drafts_scheduled_and_health_section_articles(): void
    {
        Article::factory()->draft()->for($this->tribunes, 'category')->create(['title' => 'Brouillon invisible']);
        Article::factory()->scheduled()->for($this->tribunes, 'category')->create(['title' => 'Programme invisible']);
        Article::factory()->for(ArticleCategory::factory()->publicSection(), 'category')->create(['title' => 'Fiche sante invisible']);

        $this->get(route('news'))
            ->assertOk()
            ->assertDontSee('Brouillon invisible')
            ->assertDontSee('Programme invisible')
            ->assertDontSee('Fiche sante invisible');
    }

    public function test_home_blocks_are_cached_and_refreshed_when_an_article_changes(): void
    {
        Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Premier article']);
        $this->get(route('news'))->assertSee('Premier article');

        Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Second article']);

        $this->get(route('news'))->assertSee('Second article');
        $this->assertSame('Second article', NewsBlocks::home()['featured']->title);
    }

    public function test_category_page_lists_its_articles_with_pagination(): void
    {
        // Le bandeau « Dernière minute » affiche toutes les rubriques : on le coupe ici.
        config(['news.ticker' => false]);
        Article::factory()->count(14)->for($this->tribunes, 'category')->create();
        $other = Article::factory()->for(ArticleCategory::factory(), 'category')->create(['title' => 'Autre rubrique']);

        $this->get(route('news.category', 'tribunes'))
            ->assertOk()
            ->assertSee('Regards d\'experts.')
            ->assertSee('14 articles')
            ->assertDontSee('Autre rubrique')
            ->assertSee('Charger plus')
            ->assertSee('<link rel="next"', false);

        $this->get(route('news.category', ['slug' => 'tribunes', 'page' => 2, 'fragment' => 1]))
            ->assertOk()
            ->assertHeader('X-Next-Page', '')
            ->assertDontSee('<html', false);

        $this->assertNotNull($other);
    }

    public function test_unknown_inactive_or_health_categories_return_404(): void
    {
        ArticleCategory::factory()->inactive()->create(['slug' => 'archives']);
        ArticleCategory::factory()->publicSection()->create(['slug' => 'endometriose']);

        $this->get('/actualites/inconnue')->assertNotFound();
        $this->get('/actualites/archives')->assertNotFound();
        $this->get('/actualites/endometriose')->assertNotFound();
    }

    public function test_legacy_article_url_redirects_permanently(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create(['slug' => 'ancien-lien']);

        $this->get('/actualites/ancien-lien')->assertRedirect($article->publicUrl())->assertStatus(301);
    }

    public function test_article_in_the_wrong_category_redirects_to_its_address(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create(['slug' => 'deplace']);
        ArticleCategory::factory()->create(['slug' => 'interviews']);

        $this->get('/actualites/interviews/deplace')->assertRedirect($article->publicUrl())->assertStatus(301);
    }

    public function test_article_page_has_editorial_details_and_seo_markup(): void
    {
        $author = User::factory()->create(['name' => 'Marie Durand']);
        $older = Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Plus ancien', 'published_at' => now()->subDays(3)]);
        $article = Article::factory()->for($this->tribunes, 'category')->for($author, 'author')->create([
            'title' => 'Article complet',
            'slug' => 'article-complet',
            'excerpt' => 'Un chapeau accrocheur.',
            'meta_title' => 'Titre SEO',
            'published_at' => now()->subDay(),
        ]);
        $newer = Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Plus recent', 'published_at' => now()->subHour()]);

        $this->get($article->publicUrl())
            ->assertOk()
            ->assertSeeInOrder(['Accueil', 'Actualités', 'Tribunes', 'Article complet'])
            ->assertSee('Un chapeau accrocheur.')
            ->assertSee('Par Marie Durand')
            ->assertSee('min de lecture')
            ->assertSee('<title>Titre SEO | La Quinzaine Obstétricale</title>', false)
            ->assertSee('<link rel="canonical" href="'.$article->publicUrl().'">', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('data-reading-progress', false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php?u='.urlencode($article->publicUrl()), false)
            ->assertSee('Article précédent')
            ->assertSee($older->publicUrl())
            ->assertSee('Article suivant')
            ->assertSee($newer->publicUrl())
            ->assertSee('À lire aussi');
    }

    public function test_search_highlights_the_term_and_escapes_html(): void
    {
        config(['news.ticker' => false]);
        Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Allaitement & <b>sommeil</b>', 'excerpt' => 'Conseils pour l allaitement.']);
        Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Autre sujet', 'excerpt' => 'Rien a voir.', 'body' => '<p>Rien.</p>']);

        $this->get(route('news.search', ['q' => 'allaitement']))
            ->assertOk()
            ->assertSee('1 résultat')
            ->assertSee('<mark>Allaitement</mark> &amp; &lt;b&gt;sommeil&lt;/b&gt;', false)
            ->assertDontSee('Autre sujet');

        $this->get(route('news.search', ['q' => 'introuvable']))
            ->assertOk()
            ->assertSee('Aucun article ne correspond');
    }

    public function test_news_pages_do_not_run_one_query_per_article(): void
    {
        Article::factory()->count(2)->for($this->tribunes, 'category')->create();

        $count = function (string $url): int {
            Cache::flush();
            DB::flushQueryLog();
            $this->get($url)->assertOk();

            return count(DB::getQueryLog());
        };

        DB::enableQueryLog();
        $home = $count(route('news'));
        $category = $count(route('news.category', 'tribunes'));

        Article::factory()->count(8)->for($this->tribunes, 'category')->create();

        $this->assertSame($home, $count(route('news')));
        $this->assertSame($category, $count(route('news.category', 'tribunes')));
    }

    public function test_breaking_news_ticker_is_hidden_by_default_and_can_be_enabled(): void
    {
        Article::factory()->for($this->tribunes, 'category')->create();

        $this->get(route('news'))->assertDontSee('Dernière minute');

        config(['news.ticker' => true]);
        $this->get(route('news'))->assertSee('Dernière minute');
    }

    public function test_rss_feed_and_sitemap_list_visible_articles(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create(['title' => 'Dans le flux']);
        Article::factory()->draft()->for($this->tribunes, 'category')->create(['title' => 'Hors flux']);

        $feed = $this->get(route('news.feed'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $this->assertNotFalse(simplexml_load_string($feed->getContent()));
        $feed->assertSee('Dans le flux')->assertDontSee('Hors flux');

        $sitemap = $this->get(route('sitemap'))->assertOk();
        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));
        $sitemap->assertSee($article->publicUrl())->assertSee(route('news.category', 'tribunes'));
    }

    public function test_visitor_can_subscribe_to_the_newsletter_once(): void
    {
        $this->from(route('news'))
            ->post(route('newsletter.subscribe'), ['email' => 'Lectrice@Example.com', 'consent' => '1', ...$this->validAntiBotPayload('newsletter')])
            ->assertRedirect(route('news').'#newsletter')
            ->assertSessionHas('newsletter_status');

        $this->from(route('news'))
            ->post(route('newsletter.subscribe'), ['email' => 'lectrice@example.com', 'consent' => '1', ...$this->validAntiBotPayload('newsletter')])
            ->assertSessionHas('newsletter_status');

        $this->assertSame(1, NewsletterSubscriber::where('email', 'lectrice@example.com')->count());
    }

    public function test_newsletter_requires_consent_and_anti_bot_answer(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'a@example.com', ...$this->validAntiBotPayload('newsletter')])
            ->assertSessionHasErrors('consent', errorBag: 'newsletter');

        $this->post(route('newsletter.subscribe'), ['email' => 'a@example.com', 'consent' => '1', 'website' => 'spam'])
            ->assertSessionHasErrors('website', errorBag: 'newsletter');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
