<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'schedule_items',
        'location',
        'description',
        'image_path',
        'registration_url',
        'is_paid',
        'registration_capacity',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'schedule_items' => 'array',
            'is_paid' => 'boolean',
            'is_published' => 'boolean',
        ];
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
