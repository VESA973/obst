<?php

namespace Database\Seeders\Concerns;

use App\Support\ResponsiveImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Téléchargement d'images de démonstration (picsum.photos) vers le disque public.
 */
trait DownloadsDemoImages
{
    protected function downloadDemoImage(string $seed, string $directory = 'news-demo'): ?string
    {
        try {
            $response = Http::timeout(20)->get("https://picsum.photos/seed/{$seed}/1600/900");

            if (! $response->successful()) {
                return null;
            }

            $path = "{$directory}/{$seed}.jpg";

            if (! Storage::disk('public')->put($path, $response->body())) {
                $this->command?->warn('Écriture impossible dans storage/app/public : vérifiez les permissions.');

                return null;
            }

            // Miniatures générées tout de suite pour que la première visite soit rapide.
            ResponsiveImage::for($path);

            return $path;
        } catch (Throwable) {
            // Pas de réseau : l'article utilisera le visuel de remplacement.
            return null;
        }
    }
}
