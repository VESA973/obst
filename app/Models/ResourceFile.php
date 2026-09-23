<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    use DeletesPublicFiles, HasFactory;

    /** @var list<string> Colonnes contenant un chemin sur le disque public. */
    protected array $publicFiles = ['path'];

    protected $fillable = [
        'title',
        'audience',
        'category',
        'description',
        'path',
        'original_name',
    ];
}
