<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Models\Concerns\DeletesPublicFiles;
use App\Observers\ArticleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(ArticleObserver::class)]
class Article extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['image_path'];

    protected $fillable = [
        'title',
        'slug',
        'status',
        'article_category_id',
        'user_id',
        'source_name',
        'external_url',
        'image_path',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'published_at',
        'is_published',
    ];

    protected $attributes = [
        'status' => 'published',
        'is_published' => true,
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            // Le slug n'est généré qu'à partir du titre s'il est vide : les URL publiques restent stables.
            $article->slug = static::uniqueSlug($article->slug ?: $article->title, $article->id);

            $article->normalizePublication();
        });

        // Supprime les photos une par une pour effacer aussi leurs fichiers.
        static::deleting(fn (Article $article) => $article->assets->each->delete());
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ArticleAsset::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /**
     * Ancien nom de la relation, encore utilisé par le site public.
     */
    public function categoryModel(): BelongsTo
    {
        return $this->category();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getDisplayCategoryAttribute(): string
    {
        return $this->categoryModel?->name ?: 'Article';
    }

    /**
     * Statut réel à l'instant présent : un article programmé dont la date est passée est publié.
     */
    public function getCurrentStatusAttribute(): ArticleStatus
    {
        if (! $this->is_published) {
            return ArticleStatus::Draft;
        }

        return $this->published_at?->isFuture() ? ArticleStatus::Scheduled : ArticleStatus::Published;
    }

    /**
     * Adresse publique de l'article, ou null s'il n'est pas visible.
     */
    public function publicUrl(): ?string
    {
        if (! $this->isOnSite()) {
            return null;
        }

        return $this->categoryModel?->section === 'public'
            ? route('public.article', [$this->categoryModel, $this])
            : route('news.show', ['category' => $this->categoryModel->slug, 'article' => $this->slug]);
    }

    /**
     * Temps de lecture estimé, en minutes (200 mots par minute).
     */
    public function readingTime(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags((string) $this->body.' '.$this->excerpt)), flags: PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words) / 200));
    }

    /**
     * Résumé affichable : l'extrait, ou le début du contenu.
     */
    public function summary(int $limit = 180): string
    {
        return $this->excerpt ?: Str::limit(html_entity_decode(strip_tags((string) $this->body)), $limit);
    }

    /**
     * Articles visibles sur le site public.
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Articles visibles dont la catégorie n'est pas désactivée.
     */
    #[Scope]
    protected function onSite(Builder $query): void
    {
        $query->visible()->where(fn (Builder $query) => $query
            ->whereDoesntHave('category')
            ->orWhereHas('category', fn (Builder $query) => $query->where('is_active', true)));
    }

    public function isOnSite(): bool
    {
        return $this->current_status === ArticleStatus::Published && ($this->categoryModel?->is_active ?? true);
    }

    #[Scope]
    protected function scheduled(Builder $query): void
    {
        $query->where('is_published', true)->where('published_at', '>', now());
    }

    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->where('is_published', false);
    }

    /**
     * Garde status, is_published et published_at cohérents entre eux.
     */
    public function normalizePublication(): void
    {
        $status = $this->status ?? ArticleStatus::Published;

        if ($status !== ArticleStatus::Draft) {
            $this->published_at ??= now();
            $status = $this->published_at->isFuture() ? ArticleStatus::Scheduled : ArticleStatus::Published;
        }

        $this->status = $status;
        $this->is_published = $status !== ArticleStatus::Draft;
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'actualite';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
