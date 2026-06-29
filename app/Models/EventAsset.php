<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'path',
        'original_name',
        'title',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
