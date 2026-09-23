<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\ArticleCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * Liste des articles : globale (/admin/articles) ou limitée à une catégorie
 * (/admin/articles/categorie/{slug}).
 */
class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    /** Slug de la catégorie affichée, issu de la route. Verrouillé côté client. */
    #[Locked]
    public ?string $categorie = null;

    public function mount(?string $categorie = null): void
    {
        $this->categorie = $categorie;

        // Slug inconnu : 404.
        if (filled($categorie) && ! $this->category) {
            abort(404);
        }

        parent::mount();
    }

    #[Computed]
    public function category(): ?ArticleCategory
    {
        return filled($this->categorie)
            ? ArticleCategory::query()->where('slug', $this->categorie)->first()
            : null;
    }

    public function table(Table $table): Table
    {
        $table = parent::table($table)
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['category', 'author'])
                ->when($this->category, fn (Builder $query, ArticleCategory $category) => $query->whereBelongsTo($category, 'category')));

        // Dans une catégorie, la colonne et le filtre « Catégorie » sont inutiles.
        if ($this->category) {
            $table->getColumn('category.name')?->hidden();
            $table->getFilter('article_category_id')?->hidden();
        }

        return $table;
    }

    public function getTabs(): array
    {
        $base = fn (): Builder => Article::query()->when($this->category, fn (Builder $query, ArticleCategory $category) => $query->whereBelongsTo($category, 'category'));

        return [
            'all' => Tab::make('Tous')
                ->badge($base()->count()),
            'published' => Tab::make('Publiés')
                ->modifyQueryUsing(fn (Builder $query) => $query->visible())
                ->badge($base()->visible()->count())
                ->badgeColor('success'),
            'draft' => Tab::make('Brouillons')
                ->modifyQueryUsing(fn (Builder $query) => $query->draft())
                ->badge($base()->draft()->count())
                ->badgeColor('gray'),
            'scheduled' => Tab::make('Programmés')
                ->modifyQueryUsing(fn (Builder $query) => $query->scheduled())
                ->badge($base()->scheduled()->count())
                ->badgeColor('warning'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->category?->name ?? 'Tous les articles';
    }

    public function getSubheading(): ?string
    {
        return $this->category?->description;
    }

    public function getBreadcrumbs(): array
    {
        return [
            ArticleResource::getUrl('index') => 'Gestion des articles',
            $this->category?->name ?? 'Tous les articles',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel article')
                ->url(ArticleResource::getUrl('create', array_filter(['categorie' => $this->categorie]))),
        ];
    }
}
