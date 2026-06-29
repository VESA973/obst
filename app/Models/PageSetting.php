<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PageSetting extends Model
{
    protected $fillable = [
        'page_key',
        'menu_label',
        'eyebrow',
        'title',
        'description',
        'hero_image_path',
        'title_size',
        'show_in_menu',
    ];

    protected function casts(): array
    {
        return [
            'show_in_menu' => 'boolean',
        ];
    }

    public static function defaults(): array
    {
        return config('site_pages', []);
    }

    public static function forKey(string $key): array
    {
        $default = self::defaults()[$key] ?? [];

        if (! Schema::hasTable('page_settings')) {
            return $default;
        }

        $setting = self::where('page_key', $key)->first();

        return array_merge($default, array_filter($setting?->only([
            'menu_label',
            'eyebrow',
            'title',
            'description',
            'hero_image_path',
            'title_size',
        ]) ?? [], fn ($value) => $value !== null && $value !== ''), [
            'show_in_menu' => $setting?->show_in_menu ?? ($default['show_in_menu'] ?? true),
        ]);
    }

    public static function allConfigured(): Collection
    {
        $stored = Schema::hasTable('page_settings')
            ? self::get()->keyBy('page_key')
            : collect();

        return collect(self::defaults())->map(function (array $default, string $key) use ($stored): array {
            $setting = $stored->get($key);
            $storedValues = array_filter($setting?->only([
                'menu_label',
                'eyebrow',
                'title',
                'description',
                'hero_image_path',
                'title_size',
            ]) ?? [], fn ($value) => $value !== null && $value !== '');

            return array_merge($default, $storedValues, [
                'page_key' => $key,
                'show_in_menu' => $setting?->show_in_menu ?? ($default['show_in_menu'] ?? true),
            ]);
        });
    }

    public static function menuPages(): Collection
    {
        return self::allConfigured()
            ->filter(fn (array $page): bool => (bool) ($page['show_in_menu'] ?? true))
            ->filter(fn (array $page): bool => ! empty($page['route']));
    }
}
