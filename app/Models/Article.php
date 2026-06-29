<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'article_category_id',
        'category',
        'source_name',
        'external_url',
        'image_path',
        'excerpt',
        'body',
        'published_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ArticleAsset::class);
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function getDisplayCategoryAttribute(): string
    {
        return $this->categoryModel?->name ?: ($this->category ?: 'Article');
    }
}
