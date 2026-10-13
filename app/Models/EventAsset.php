<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAsset extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['path'];

    protected $fillable = [
        'event_id',
        'type',
        'path',
        'original_name',
        'title',
    ];

    protected static function booted(): void
    {
        // Le type se déduit du fichier : les images vont dans la galerie, le reste en documents.
        static::saving(function (EventAsset $asset): void {
            $extension = strtolower(pathinfo($asset->path ?? '', PATHINFO_EXTENSION));
            $asset->type = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? 'photo' : 'document';
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
