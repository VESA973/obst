<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\ArticleCategory;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Locked;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /** Catégorie d'origine (?categorie=slug) pour pré-remplir le formulaire. */
    #[Locked]
    public ?string $categorie = null;

    public function mount(): void
    {
        $this->categorie = request()->query('categorie');

        parent::mount();
    }

    protected function afterFill(): void
    {
        $category = filled($this->categorie)
            ? ArticleCategory::query()->where('slug', $this->categorie)->first()
            : null;

        // Depuis une catégorie : rubrique et catégorie pré-remplies. Sinon, l'auteur choisit sa rubrique.
        if ($category) {
            $this->data['rubrique'] = $category->section;
            $this->data['article_category_id'] = $category->id;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    public function getBreadcrumbs(): array
    {
        $category = filled($this->categorie) ? ArticleCategory::query()->where('slug', $this->categorie)->first() : null;

        return array_filter([
            ArticleResource::getUrl('index') => 'Gestion des articles',
            ...($category ? [ArticleResource::getUrl('category', ['categorie' => $category->slug]) => $category->name] : []),
            'Nouvel article',
        ]);
    }

    /**
     * Retour à la liste de la catégorie de l'article créé.
     */
    protected function getRedirectUrl(): string
    {
        return ArticleResource::getUrl('category', ['categorie' => $this->record->category->slug]);
    }
}
