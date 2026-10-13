<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['image_path'];

    protected $fillable = [
        'title',
        'event_date',
        'schedule_items',
        'location',
        'description',
        'image_path',
        'banner_position',
        'flyer_position',
        'registration_url',
        'is_paid',
        'is_free_entry',
        'registration_capacity',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'schedule_items' => 'array',
            'is_paid' => 'boolean',
            'is_free_entry' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            // Sans date principale, on reprend la première date du programme.
            if (! $event->event_date && filled($event->schedule_items)) {
                $event->event_date = collect($event->schedule_items)->pluck('date')->filter()->sort()->first();
            }
        });

        // Supprime photos et documents un par un pour effacer aussi leurs fichiers.
        static::deleting(fn (Event $event) => $event->assets->each->delete());
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(EventAsset::class);
    }

    public function getFlyerIsImageAttribute(): bool
    {
        if (! $this->image_path) {
            return false;
        }

        return in_array(strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION)), [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif',
            'svg',
        ], true);
    }

    public function getFlyerExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->image_path ?? '', PATHINFO_EXTENSION) ?: 'FICHIER');
    }
}
