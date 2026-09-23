<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use App\Observers\ArticleCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[ObservedBy(ArticleCategoryObserver::class)]
class ArticleCategory extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['image_path'];

    /** Clé de cache de la liste utilisée par la navigation de l'administration. */
    public const NAVIGATION_CACHE_KEY = 'admin.article-categories.navigation.v2';

    public const SECTIONS = [
        'news' => 'Actualités',
        'public' => 'Santé de la femme',
    ];

    protected $fillable = [
        'name',
        'slug',
        'section',
        'title',
        'description',
        'image_path',
        'is_active',
        'position',
    ];

    protected $attributes = [
        'section' => 'news',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArticleCategory $category): void {
            // Nouvelle catégorie placée en fin de liste.
            if (! $category->position) {
                $category->position = (int) static::max('position') + 1;
            }
        });

        static::saving(function (ArticleCategory $category): void {
            $category->slug = static::uniqueSlug($category->slug ?: $category->name, $category->id);
        });
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }

    /**
     * Catégories actives (id, nom, slug, nombre d'articles) mises en cache pour la barre latérale.
     *
     * @return Collection<int, array{id: int, name: string, slug: string, section: string, position: int, articles_count: int}>
     */
    public static function navigationItems(): Collection
    {
        return Cache::rememberForever(self::NAVIGATION_CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->withCount('articles')
            ->get(['id', 'name', 'slug', 'section', 'position'])
            ->map(fn (ArticleCategory $category) => $category->only(['id', 'name', 'slug', 'section', 'position', 'articles_count']))
            ->values());
    }

    public static function forgetNavigationCache(): void
    {
        Cache::forget(self::NAVIGATION_CACHE_KEY);
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'categorie';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
