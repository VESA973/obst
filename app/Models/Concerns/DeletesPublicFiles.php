<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Supprime du disque « public » les fichiers remplacés ou dont l'enregistrement est supprimé.
 * Le modèle déclare ses colonnes de fichiers dans la propriété $publicFiles.
 */
trait DeletesPublicFiles
{
    protected static function bootDeletesPublicFiles(): void
    {
        static::updated(function (self $model): void {
            foreach ($model->publicFiles as $attribute) {
                $original = $model->getOriginal($attribute);

                if ($model->wasChanged($attribute) && self::isStoredFile($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        });

        static::deleted(function (self $model): void {
            foreach ($model->publicFiles as $attribute) {
                if (self::isStoredFile($model->getAttribute($attribute))) {
                    Storage::disk('public')->delete($model->getAttribute($attribute));
                }
            }
        });
    }

    /**
     * Les images par défaut (dossier images/ du site) ne sont jamais supprimées.
     */
    private static function isStoredFile(?string $path): bool
    {
        return filled($path) && ! str_starts_with($path, 'images/');
    }
}
