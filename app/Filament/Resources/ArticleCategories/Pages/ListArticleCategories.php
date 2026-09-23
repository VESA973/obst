<?php

namespace App\Filament\Resources\ArticleCategories\Pages;

use App\Filament\Resources\ArticleCategories\ArticleCategoryResource;
use App\Models\ArticleCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * Catégories classées par rubrique : un onglet par rubrique, réordonnables à l'intérieur de chacune.
 */
class ListArticleCategories extends ListRecords
{
    protected static string $resource = ArticleCategoryResource::class;

    public function getTabs(): array
    {
        $counts = ArticleCategory::query()->selectRaw('section, count(*) as total')->groupBy('section')->pluck('total', 'section');

        $tabs = [
            'all' => Tab::make('Toutes')->badge($counts->sum()),
        ];

        foreach (ArticleCategory::SECTIONS as $section => $label) {
            $tabs[$section] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('section', $section))
                ->badge($counts->get($section, 0));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Depuis un onglet de rubrique, la nouvelle catégorie y est rangée par défaut.
            CreateAction::make()
                ->label('Nouvelle catégorie')
                ->url(fn () => ArticleCategoryResource::getUrl('create', array_filter([
                    'rubrique' => array_key_exists((string) $this->activeTab, ArticleCategory::SECTIONS) ? $this->activeTab : null,
                ]))),
        ];
    }
}
