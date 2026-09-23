<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use App\Support\ResponsiveImage;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Photo de la page « L'association » : un emplacement fixe (hero, mission…) ou une image de galerie.
 */
class AssociationPageImage extends Model
{
    use DeletesPublicFiles;

    /** Emplacements à image unique, avec leur libellé et leur ratio de recadrage. */
    public const SLOTS = [
        'hero' => ['label' => 'Bannière (haut de page)', 'ratio' => '16:9'],
        'mission' => ['label' => 'Section « Notre mission »', 'ratio' => '4:3'],
        'history' => ['label' => 'Section « Notre histoire »', 'ratio' => '4:3'],
        'actions' => ['label' => 'Section « Nos actions en Guyane »', 'ratio' => '4:3'],
    ];

    public const GALLERY = 'gallery';

    public const CACHE_KEY = 'association.page-images';

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['path'];

    protected $fillable = [
        'slot',
        'path',
        'alt',
        'caption',
        'position',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Cache vidé à chaque enregistrement ; miniatures WebP supprimées avec l'image.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(function (AssociationPageImage $image): void {
            Cache::forget(self::CACHE_KEY);
            ResponsiveImage::forget($image->path);
        });
        static::updated(function (AssociationPageImage $image): void {
            if ($image->wasChanged('path')) {
                ResponsiveImage::forget($image->getOriginal('path'));
            }
        });
    }

    #[Scope]
    protected function gallery(Builder $query): void
    {
        $query->where('slot', self::GALLERY)->orderBy('position')->orderBy('id');
    }

    /**
     * Images de la page, mises en cache : ['slots' => [slot => image], 'gallery' => images visibles].
     *
     * @return array{slots: Collection<string, self>, gallery: Collection<int, self>}
     */
    public static function forPage(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => [
            'slots' => static::query()->whereIn('slot', array_keys(self::SLOTS))->get()->keyBy('slot'),
            'gallery' => static::query()->gallery()->where('is_visible', true)->get(),
        ]);
    }
}
