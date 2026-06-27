<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'audience',
        'category',
        'description',
        'path',
        'original_name',
    ];
}
