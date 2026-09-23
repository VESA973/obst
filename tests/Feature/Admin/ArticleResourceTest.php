<?php

namespace Tests\Feature\Admin;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ArticleCategory $tribunes;

    private ArticleCategory $interviews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);

        $this->tribunes = ArticleCategory::factory()->create(['name' => 'Tribunes', 'slug' => 'tribunes', 'position' => 1]);
        $this->interviews = ArticleCategory::factory()->create(['name' => 'Interviews', 'slug' => 'interviews', 'position' => 2]);
    }

    public function test_list_is_filtered_by_category(): void
    {
        $tribunes = Article::factory()->count(2)->for($this->tribunes, 'category')->create();
        $interviews = Article::factory()->count(2)->for($this->interviews, 'category')->create();

        Livewire::test(ListArticles::class, ['categorie' => 'tribunes'])
            ->assertCanSeeTableRecords($tribunes)
            ->assertCanNotSeeTableRecords($interviews)
            ->assertSee('Tribunes');
    }

    public function test_global_list_shows_every_category(): void
    {
        $articles = Article::factory()->count(2)->for($this->tribunes, 'category')->create()
            ->merge(Article::factory()->count(2)->for($this->interviews, 'category')->create());

        Livewire::test(ListArticles::class)
            ->assertCanSeeTableRecords($articles)
            ->assertTableColumnVisible('category.name');
    }

    public function test_unknown_category_slug_returns_404(): void
    {
        Livewire::test(ListArticles::class, ['categorie' => 'inexistante'])->assertNotFound();
    }

    public function test_unknown_category_url_returns_404(): void
    {
        $this->get(ArticleResource::getUrl('category', ['categorie' => 'inexistante']))->assertNotFound();
    }

    public function test_status_tabs_filter_the_list(): void
    {
        $published = Article::factory()->for($this->tribunes, 'category')->create();
        $draft = Article::factory()->draft()->for($this->tribunes, 'category')->create();
        $scheduled = Article::factory()->scheduled()->for($this->tribunes, 'category')->create();

        Livewire::test(ListArticles::class, ['categorie' => 'tribunes'])
            ->set('activeTab', 'draft')
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published, $scheduled])
            ->set('activeTab', 'scheduled')
            ->assertCanSeeTableRecords([$scheduled])
            ->assertCanNotSeeTableRecords([$published, $draft]);
    }

    public function test_rubriques_are_collapsible_sub_menus_of_article_management(): void
    {
        ArticleCategory::factory()->inactive()->create(['name' => 'Archives']);
        // Même nom que sa rubrique : le rattachement se fait par clé, pas par libellé.
        ArticleCategory::factory()->publicSection()->create(['name' => 'Santé de la femme', 'slug' => 'sante', 'position' => 3]);
        Article::factory()->count(2)->for($this->tribunes, 'category')->create();

        $this->get(ArticleResource::getUrl('category', ['categorie' => 'tribunes']))
            ->assertOk()
            ->assertSeeInOrder(['Gestion des articles', 'Création des catégories', 'Nouvel article', 'Actualités', 'Tribunes', 'Interviews', 'Santé de la femme'])
            ->assertSee('admin-rubriques-fermees', false)
            ->assertDontSee('Archives')
            ->assertDontSee('Tous les articles');

        $items = collect(ArticleResource::getNavigationItems());

        $this->assertSame(
            ['Nouvel article', 'Actualités', 'Tribunes', 'Interviews', 'Santé de la femme', 'Santé de la femme'],
            $items->map->getLabel()->all(),
        );
        $this->assertSame(
            [null, null, 'article-section.news', 'article-section.news', null, 'article-section.public'],
            $items->map->getParentItem()->all(),
        );
        $this->assertSame(['Gestion des articles'], $items->map->getGroup()->unique()->values()->all());
    }

    public function test_category_choices_depend_on_the_chosen_rubrique(): void
    {
        $health = ArticleCategory::factory()->publicSection()->create(['name' => 'Endométriose']);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Mauvaise combinaison',
                'rubrique' => 'news',
                'article_category_id' => $health->id,
                'status' => ArticleStatus::Published->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['article_category_id']);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Sans rubrique', 'status' => ArticleStatus::Published->value])
            ->call('create')
            ->assertHasFormErrors(['rubrique' => 'required']);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Bonne combinaison',
                'rubrique' => 'public',
                'article_category_id' => $health->id,
                'status' => ArticleStatus::Published->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($health->id, Article::firstWhere('title', 'Bonne combinaison')->article_category_id);
    }

    public function test_edit_form_shows_the_rubrique_of_the_article(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->assertSchemaStateSet(['rubrique' => 'news', 'article_category_id' => $this->tribunes->id]);
    }

    public function test_create_page_prefills_the_category_and_redirects_to_it(): void
    {
        $this->get(ArticleResource::getUrl('create', ['categorie' => 'interviews']))->assertOk();

        Livewire::withQueryParams(['categorie' => 'interviews'])
            ->test(CreateArticle::class)
            ->assertSchemaStateSet(['rubrique' => 'news', 'article_category_id' => $this->interviews->id])
            ->fillForm([
                'title' => 'Entretien avec une sage-femme',
                'status' => ArticleStatus::Published->value,
                'body' => '<p>Contenu</p>',
            ])
            ->assertSchemaStateSet(['slug' => 'entretien-avec-une-sage-femme'])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(ArticleResource::getUrl('category', ['categorie' => 'interviews']));

        $article = Article::where('slug', 'entretien-avec-une-sage-femme')->firstOrFail();

        $this->assertSame($this->interviews->id, $article->article_category_id);
        $this->assertSame($this->admin->id, $article->user_id);
        $this->assertTrue($article->is_published);
        $this->assertNotNull($article->published_at);
    }

    public function test_scheduled_article_requires_a_future_date(): void
    {
        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Article programme',
                'rubrique' => 'news',
                'article_category_id' => $this->tribunes->id,
                'status' => ArticleStatus::Scheduled->value,
                'published_at' => now()->subDay(),
            ])
            ->call('create')
            ->assertHasFormErrors(['published_at' => 'after']);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Article programme',
                'rubrique' => 'news',
                'article_category_id' => $this->tribunes->id,
                'status' => ArticleStatus::Scheduled->value,
                'published_at' => now()->addDays(3),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Article programme')->firstOrFail();
        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->assertSame(ArticleStatus::Scheduled, $article->current_status);
    }

    public function test_editing_the_category_moves_the_article_and_redirects_to_its_new_list(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['article_category_id' => $this->interviews->id])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ArticleResource::getUrl('category', ['categorie' => 'interviews']));

        $this->assertSame($this->interviews->id, $article->refresh()->article_category_id);
    }

    public function test_deleting_from_the_edit_page_returns_to_the_category_list(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->callAction(DeleteAction::class)
            ->assertRedirect(ArticleResource::getUrl('category', ['categorie' => 'tribunes']));

        $this->assertModelMissing($article);
    }

    public function test_bulk_actions_publish_unpublish_and_move(): void
    {
        $drafts = Article::factory()->count(2)->draft()->for($this->tribunes, 'category')->create();

        $list = Livewire::test(ListArticles::class, ['categorie' => 'tribunes']);

        $list->selectTableRecords($drafts)->callAction(TestAction::make('publish')->table()->bulk());
        $drafts->each(fn (Article $article) => $this->assertSame(ArticleStatus::Published, $article->refresh()->current_status));

        $list->selectTableRecords($drafts)->callAction(TestAction::make('unpublish')->table()->bulk());
        $drafts->each(fn (Article $article) => $this->assertFalse($article->refresh()->is_published));

        $list->selectTableRecords($drafts)
            ->callAction(TestAction::make('move')->table()->bulk(), data: ['article_category_id' => $this->interviews->id])
            ->assertHasNoActionErrors();
        $drafts->each(fn (Article $article) => $this->assertSame($this->interviews->id, $article->refresh()->article_category_id));
    }

    public function test_move_bulk_action_requires_a_category(): void
    {
        $article = Article::factory()->for($this->tribunes, 'category')->create();

        Livewire::test(ListArticles::class, ['categorie' => 'tribunes'])
            ->selectTableRecords([$article])
            ->callAction(TestAction::make('move')->table()->bulk(), data: ['article_category_id' => null])
            ->assertHasActionErrors(['article_category_id' => 'required']);
    }

    public function test_non_admin_cannot_reach_article_pages(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get(ArticleResource::getUrl('category', ['categorie' => 'tribunes']))->assertForbidden();
        $this->get(ArticleResource::getUrl('create'))->assertForbidden();
    }
}
