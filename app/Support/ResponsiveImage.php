<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Miniatures WebP (400, 800 et 1200 px de large) générées à la première demande
 * dans storage/app/public/thumbs, pour alimenter les attributs srcset.
 */
class ResponsiveImage
{
    public const WIDTHS = [400, 800, 1200];

    /**
     * @return array{src: string, srcset: string}|null null si l'image n'existe pas.
     */
    public static function for(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $sources = [];

        foreach (self::WIDTHS as $width) {
            $thumbnail = self::thumbnailPath($path, $width);

            if (! $disk->exists($thumbnail) && ! self::generate($path, $thumbnail, $width)) {
                // Conversion impossible (format non géré…) : on sert l'original.
                return ['src' => $disk->url($path), 'srcset' => ''];
            }

            $sources[$width] = $disk->url($thumbnail);
        }

        return [
            'src' => $sources[800],
            'srcset' => collect($sources)->map(fn (string $url, int $width) => "{$url} {$width}w")->implode(', '),
        ];
    }

    /**
     * Supprime les miniatures d'une image (remplacée ou supprimée).
     */
    public static function forget(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        foreach (self::WIDTHS as $width) {
            Storage::disk('public')->delete(self::thumbnailPath($path, $width));
        }
    }

    public static function thumbnailPath(string $path, int $width): string
    {
        return 'thumbs/'.$width.'/'.preg_replace('/\.[^.\/]+$/', '', $path).'.webp';
    }

    private static function generate(string $path, string $thumbnail, int $width): bool
    {
        $disk = Storage::disk('public');

        try {
            $image = (new ImageManager(new Driver))->read($disk->path($path));

            // Jamais d'agrandissement : une petite image garde sa taille.
            $encoded = $image->scaleDown(width: $width)->toWebp(quality: 78);

            $disk->put($thumbnail, (string) $encoded);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Miniature impossible pour '.$path.' : '.$exception->getMessage());

            return false;
        }
    }
}
