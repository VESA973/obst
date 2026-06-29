<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'type',
        'path',
        'original_name',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
