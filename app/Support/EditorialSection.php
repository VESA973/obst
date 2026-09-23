<?php

namespace App\Support;

use App\Models\ArticleCategory;
use Illuminate\Support\Collection;

/**
 * Décrit une section éditoriale publique (« Actualités » ou « Santé de la femme »).
 * Les deux sections partagent les mêmes vues (resources/views/news) et composants :
 * seuls les libellés, les adresses et les options diffèrent.
 */
class EditorialSection
{
    private function __construct(
        /** Valeur de article_categories.section. */
        public readonly string $key,
        public readonly string $title,
        public readonly string $description,
        public readonly string $homeRoute,
        /** Route de recherche et paramètre du terme (même paramètre q partout). */
        public readonly string $searchRoute,
        public readonly ?string $feedRoute,
        public readonly bool $ticker,
        /** Sommaire et avertissement médical sur les pages article. */
        public readonly bool $health,
    ) {}

    public static function news(): self
    {
        return new self(
            key: 'news',
            title: 'Actualités',
            description: 'Tribunes, interviews et publications sur la santé des femmes et la périnatalité en Guyane.',
            homeRoute: 'news',
            searchRoute: 'news.search',
            feedRoute: 'news.feed',
            ticker: (bool) config('news.ticker'),
            health: false,
        );
    }

    public static function health(): self
    {
        return new self(
            key: 'public',
            title: 'Santé de la femme',
            description: 'Des repères simples, classés par thème, pour chaque étape de la vie des femmes.',
            homeRoute: 'public',
            // Recherche sur la page d'accueil de la section (/sante-des-femmes?q=…).
            searchRoute: 'public',
            feedRoute: null,
            ticker: false,
            health: true,
        );
    }

    /**
     * Section de la page en cours, déduite de la route.
     */
    public static function current(): self
    {
        return request()->routeIs('public', 'public.*') ? self::health() : self::news();
    }

    /**
     * Section d'une catégorie donnée.
     */
    public static function forCategory(?ArticleCategory $category): self
    {
        return $category?->section === 'public' ? self::health() : self::news();
    }

    public function homeUrl(): string
    {
        return route($this->homeRoute);
    }

    public function searchUrl(): string
    {
        return route($this->searchRoute);
    }

    public function feedUrl(): ?string
    {
        return $this->feedRoute ? route($this->feedRoute) : null;
    }

    /**
     * Rubriques actives de la section, avec leur nombre d'articles visibles (en cache).
     *
     * @return Collection<int, ArticleCategory>
     */
    public function categories(): Collection
    {
        return NewsBlocks::categories($this->key);
    }

    /**
     * Adresse publique d'une rubrique, quelle que soit sa section.
     */
    public static function categoryUrl(ArticleCategory $category, array $query = []): string
    {
        return $category->section === 'public'
            ? route('public.category', ['category' => $category, ...$query])
            : route('news.category', ['slug' => $category->slug, ...$query]);
    }

    /**
     * Slug de la rubrique affichée (page rubrique ou article), pour la surbrillance de la barre.
     */
    public function currentCategorySlug(): ?string
    {
        $category = request()->route('category') ?? request()->route('slug');

        return $category instanceof ArticleCategory ? $category->slug : $category;
    }
}
