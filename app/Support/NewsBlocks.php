<?php

namespace App\Support;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Données de la rubrique Actualités, mises en cache.
 * Le cache est vidé par les observers Article / ArticleCategory, et expire au bout
 * de 5 minutes pour que les articles programmés apparaissent à l'heure.
 */
class NewsBlocks
{
    private const TTL = 300;

    /** Sections éditoriales mises en cache (valeurs de article_categories.section). */
    private const SECTIONS = ['news', 'public'];

    /**
     * Articles visibles d'une section : « news » (Actualités) ou « public » (Santé de la femme).
     */
    public static function articles(string $section = 'news'): Builder
    {
        return Article::query()
            ->onSite()
            ->whereHas('category', fn (Builder $query) => $query->where('section', $section))
            // categoryModel est l'alias utilisé par Article::publicUrl() : préchargé aussi (pas de N+1).
            ->with(['category', 'categoryModel', 'author'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * Catégories actives de la rubrique, avec leur nombre d'articles visibles.
     *
     * @return Collection<int, ArticleCategory>
     */
    public static function categories(string $section = 'news'): Collection
    {
        return Cache::remember(self::key('categories', $section), self::TTL, fn () => ArticleCategory::query()
            ->where('section', $section)
            ->active()
            ->ordered()
            ->withCount(['articles' => fn (Builder $query) => $query->visible()])
            ->get());
    }

    /**
     * Blocs de la page d'accueil des actualités.
     *
     * @return array{featured: ?Article, secondary: Collection, latest: Collection, sections: Collection, recent: Collection}
     */
    public static function home(string $section = 'news'): array
    {
        return Cache::remember(self::key('home', $section), self::TTL, function () use ($section): array {
            $top = self::articles($section)->limit(17)->get();

            $sections = self::categories($section)
                ->filter(fn (ArticleCategory $category) => $category->articles_count > 0)
                ->map(fn (ArticleCategory $category) => [
                    'category' => $category,
                    'articles' => self::articles($section)->whereBelongsTo($category, 'category')->limit(4)->get(),
                ])
                ->values();

            return [
                'featured' => $top->first(),
                'secondary' => $top->slice(1, 4)->values(),
                'latest' => $top->slice(5, 12)->values(),
                'sections' => $sections,
                'recent' => $top->take(5),
            ];
        });
    }

    /**
     * Titres du bandeau « Dernière minute ».
     *
     * @return Collection<int, Article>
     */
    public static function latestTitles(string $section = 'news'): Collection
    {
        return Cache::remember(self::key('latest-titles', $section), self::TTL, fn () => self::articles($section)->limit(6)->get());
    }

    /**
     * Article précédent, suivant (dans la même rubrique) et articles similaires.
     *
     * @return array{previous: ?Article, next: ?Article, related: Collection}
     */
    public static function neighbours(Article $article): array
    {
        $sameCategory = self::articles($article->category->section)->whereBelongsTo($article->category, 'category');

        return [
            'related' => (clone $sameCategory)->whereKeyNot($article->id)->limit(3)->get(),
            // Précédent = plus ancien, suivant = plus récent.
            'previous' => (clone $sameCategory)->reorder()
                ->where(fn (Builder $query) => $query->where('published_at', '<', $article->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $article->published_at)->where('id', '<', $article->id)))
                ->orderByDesc('published_at')->orderByDesc('id')->first(),
            'next' => (clone $sameCategory)->reorder()
                ->where(fn (Builder $query) => $query->where('published_at', '>', $article->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $article->published_at)->where('id', '>', $article->id)))
                ->orderBy('published_at')->orderBy('id')->first(),
        ];
    }

    public static function flush(): void
    {
        foreach (self::SECTIONS as $section) {
            foreach (['home', 'categories', 'latest-titles'] as $block) {
                Cache::forget(self::key($block, $section));
            }
        }
    }

    /**
     * Clé de cache d'un bloc ; les Actualités gardent leurs clés historiques (news.home…).
     */
    private static function key(string $block, string $section): string
    {
        return $section === 'news' ? "news.{$block}" : "sante.{$block}";
    }

    /**
     * Couleur d'une catégorie, choisie de façon stable dans la palette du site.
     */
    public static function color(?ArticleCategory $category): string
    {
        $palette = ['#3a00f5', '#c62bc8', '#0f766e', '#b45309', '#be123c', '#1d4ed8', '#7c3aed', '#047857'];

        return $category ? $palette[$category->id % count($palette)] : $palette[0];
    }
}
