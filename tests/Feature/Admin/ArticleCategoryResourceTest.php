<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ArticleCategories\ArticleCategoryResource;
use App\Filament\Resources\ArticleCategories\Pages\CreateArticleCategory;
use App\Filament\Resources\ArticleCategories\Pages\EditArticleCategory;
use App\Filament\Resources\ArticleCategories\Pages\ListArticleCategories;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_create_a_category_with_generated_slug(): void
    {
        Livewire::test(CreateArticleCategory::class)
            ->fillForm(['name' => 'Santé mentale', 'section' => 'public'])
            ->assertSchemaStateSet(['slug' => 'sante-mentale'])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = ArticleCategory::where('name', 'Santé mentale')->firstOrFail();

        $this->assertSame('sante-mentale', $category->slug);
        $this->assertSame('public', $category->section);
        $this->assertTrue($category->is_active);
        $this->assertSame(1, $category->position);
    }

    public function test_categories_are_listed_by_rubrique_tabs(): void
    {
        $news = ArticleCategory::factory()->create(['name' => 'Tribunes']);
        $health = ArticleCategory::factory()->publicSection()->create(['name' => 'Endométriose']);

        Livewire::test(ListArticleCategories::class)
            ->assertCanSeeTableRecords([$news, $health], inOrder: true)
            ->set('activeTab', 'public')
            ->assertCanSeeTableRecords([$health])
            ->assertCanNotSeeTableRecords([$news])
            ->assertActionHasUrl('create', ArticleCategoryResource::getUrl('create', ['rubrique' => 'public']));
    }

    public function test_create_form_prefills_the_rubrique_from_the_tab(): void
    {
        $this->get(ArticleCategoryResource::getUrl('create', ['rubrique' => 'public']))->assertOk();

        Livewire::withQueryParams(['rubrique' => 'public'])
            ->test(CreateArticleCategory::class)
            ->assertSchemaStateSet(['section' => 'public']);

        Livewire::withQueryParams(['rubrique' => 'inconnue'])
            ->test(CreateArticleCategory::class)
            ->assertSchemaStateSet(['section' => 'news']);
    }

    public function test_category_name_and_slug_must_be_unique(): void
    {
        ArticleCategory::factory()->create(['name' => 'Prévention', 'slug' => 'prevention']);

        Livewire::test(CreateArticleCategory::class)
            ->fillForm(['name' => 'Prévention', 'slug' => 'prevention', 'section' => 'news'])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique', 'slug' => 'unique']);
    }

    public function test_list_is_sorted_by_position_and_counts_articles(): void
    {
        $second = ArticleCategory::factory()->create(['name' => 'Deuxième', 'position' => 2]);
        $first = ArticleCategory::factory()->create(['name' => 'Première', 'position' => 1]);
        Article::factory()->count(3)->for($second, 'category')->create();

        Livewire::test(ListArticleCategories::class)
            ->assertCanSeeTableRecords([$first, $second], inOrder: true)
            ->assertTableColumnStateSet('articles_count', 3, $second);
    }

    public function test_empty_category_is_deleted_directly(): void
    {
        $category = ArticleCategory::factory()->create();

        Livewire::test(ListArticleCategories::class)
            ->callAction(TestAction::make('delete')->table($category))
            ->assertHasNoFormErrors();

        $this->assertModelMissing($category);
    }

    public function test_deleting_a_category_with_articles_requires_a_target(): void
    {
        $category = ArticleCategory::factory()->create();
        Article::factory()->for($category, 'category')->create();

        Livewire::test(ListArticleCategories::class)
            ->callAction(TestAction::make('delete')->table($category), data: ['reassign_to' => null])
            ->assertHasActionErrors(['reassign_to' => 'required']);

        $this->assertModelExists($category);
    }

    public function test_articles_are_reassigned_before_deletion(): void
    {
        $category = ArticleCategory::factory()->create();
        $target = ArticleCategory::factory()->create();
        $articles = Article::factory()->count(2)->for($category, 'category')->create();

        Livewire::test(ListArticleCategories::class)
            ->callAction(TestAction::make('delete')->table($category), data: ['reassign_to' => $target->id])
            ->assertHasNoActionErrors();

        $this->assertModelMissing($category);
        $articles->each(fn (Article $article) => $this->assertSame($target->id, $article->refresh()->article_category_id));
    }

    public function test_deleting_from_the_edit_page_reassigns_and_returns_to_the_list(): void
    {
        $category = ArticleCategory::factory()->create();
        $target = ArticleCategory::factory()->create();
        $article = Article::factory()->for($category, 'category')->create();

        Livewire::test(EditArticleCategory::class, ['record' => $category->getRouteKey()])
            ->callAction(TestAction::make('delete'), data: ['reassign_to' => $target->id])
            ->assertHasNoActionErrors()
            ->assertRedirect(ArticleCategoryResource::getUrl('index'));

        $this->assertModelMissing($category);
        $this->assertSame($target->id, $article->refresh()->article_category_id);
    }

    public function test_bulk_delete_skips_categories_that_contain_articles(): void
    {
        $empty = ArticleCategory::factory()->create();
        $filled = ArticleCategory::factory()->create();
        Article::factory()->for($filled, 'category')->create();

        Livewire::test(ListArticleCategories::class)
            ->selectTableRecords([$empty, $filled])
            ->callAction(TestAction::make('delete')->table()->bulk());

        $this->assertModelMissing($empty);
        $this->assertModelExists($filled);
    }

    public function test_navigation_cache_is_refreshed_when_categories_change(): void
    {
        ArticleCategory::factory()->create(['name' => 'Tribunes']);
        $this->assertSame(['Tribunes'], ArticleCategory::navigationItems()->pluck('name')->all());

        $interviews = ArticleCategory::factory()->create(['name' => 'Interviews']);
        $this->assertSame(['Tribunes', 'Interviews'], ArticleCategory::navigationItems()->pluck('name')->all());

        $interviews->update(['is_active' => false]);
        $this->assertSame(['Tribunes'], ArticleCategory::navigationItems()->pluck('name')->all());

        $this->assertTrue(Cache::has(ArticleCategory::NAVIGATION_CACHE_KEY));
        Article::factory()->for(ArticleCategory::first(), 'category')->create();
        $this->assertFalse(Cache::has(ArticleCategory::NAVIGATION_CACHE_KEY));
    }
}
