<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleAsset extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['path'];

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
