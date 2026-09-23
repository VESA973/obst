<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Voir sur le site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (Article $record) => $record->publicUrl(), shouldOpenInNewTab: true)
                ->visible(fn (Article $record) => $record->publicUrl() !== null),
            // Adresse de retour calculée avant la suppression : ensuite, l'article n'est plus transmis.
            DeleteAction::make()
                ->successRedirectUrl($this->categoryListUrl($this->record)),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ArticleResource::getUrl('index') => 'Gestion des articles',
            $this->categoryListUrl($this->record) => $this->record->category?->name ?? 'Tous les articles',
            'Modifier',
        ];
    }

    /**
     * Retour à la liste de la catégorie (éventuellement nouvelle) de l'article.
     */
    protected function getRedirectUrl(): string
    {
        return $this->categoryListUrl($this->record->refresh());
    }

    private function categoryListUrl(Article $article): string
    {
        return $article->category
            ? ArticleResource::getUrl('category', ['categorie' => $article->category->slug])
            : ArticleResource::getUrl('index');
    }
}
