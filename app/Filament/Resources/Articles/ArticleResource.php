<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Filament\Resources\Articles\Tables\ArticlesTable;
use App\Models\Article;
use App\Models\ArticleCategory;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

use function Filament\Support\original_request;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion des articles';

    protected static ?string $navigationLabel = 'Tous les articles';

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'articles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }

    /**
     * Navigation de « Gestion des articles » : un accès « Nouvel article », puis chaque rubrique
     * (Actualités, Santé de la femme) avec ses catégories actives en sous-menu repliable
     * (liste mise en cache ; repli géré par resources/views/filament/sidebar-rubriques.blade.php).
     *
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $currentCategory = static::currentCategorySlug();
        $categories = ArticleCategory::navigationItems()->groupBy('section');

        $items = [
            NavigationItem::make('Nouvel article')
                ->key(static::class.'.create')
                ->group(static::getNavigationGroup())
                ->icon(Heroicon::OutlinedPencilSquare)
                ->url(static::getUrl('create'))
                ->isActiveWhen(fn (): bool => original_request()->routeIs(static::getRouteBaseName().'.create'))
                ->sort(1),
        ];

        $sort = 1;

        foreach (ArticleCategory::SECTIONS as $section => $label) {
            if (! $categories->has($section)) {
                continue;
            }

            // Rubrique : sans lien, elle sert de titre repliable à ses catégories.
            $items[] = NavigationItem::make($label)
                ->key(static::sectionNavigationKey($section))
                ->group(static::getNavigationGroup())
                ->icon($section === 'public' ? Heroicon::OutlinedHeart : Heroicon::OutlinedNewspaper)
                ->sort(++$sort);

            foreach ($categories[$section] as $category) {
                $items[] = NavigationItem::make($category['name'])
                    ->key(static::class.'.'.$category['slug'])
                    // Rattachement par clé : une catégorie peut porter le même nom que sa rubrique.
                    ->parentItem(static::sectionNavigationKey($section))
                    ->group(static::getNavigationGroup())
                    ->url(static::getUrl('category', ['categorie' => $category['slug']]))
                    ->isActiveWhen(fn (): bool => $currentCategory === $category['slug'] && ! original_request()->routeIs(static::getRouteBaseName().'.create'))
                    ->badge($category['articles_count'] ?: null)
                    ->sort($category['position']);
            }
        }

        return $items;
    }

    public static function sectionNavigationKey(string $section): string
    {
        return 'article-section.'.$section;
    }

    /**
     * Catégorie en cours, lue depuis la route (fiable aussi pendant les requêtes Livewire),
     * puis depuis ?categorie= (page de création) ou l'article édité.
     */
    public static function currentCategorySlug(): ?string
    {
        $request = original_request();

        if (! $request->routeIs(static::getRouteBaseName().'.*')) {
            return null;
        }

        if (filled($slug = $request->route('categorie') ?? $request->query('categorie'))) {
            return $slug;
        }

        $record = $request->route('record');

        if (filled($record)) {
            return Article::query()
                ->whereKey($record instanceof Article ? $record->getKey() : $record)
                ->first()?->category?->slug;
        }

        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'category' => ListArticles::route('/categorie/{categorie}'),
            'create' => CreateArticle::route('/creer'),
            'edit' => EditArticle::route('/{record}/modifier'),
        ];
    }
}
