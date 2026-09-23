<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Message reçu via le formulaire de la page Contact.
 */
class ContactMessage extends Model
{
    /** Motifs proposés dans le formulaire (repris de l'introduction de la page Contact). */
    public const TOPICS = [
        'question' => 'Une question',
        'proposition' => 'Une proposition',
        'partenariat' => 'Un partenariat',
        'ressource' => 'Une demande de ressource',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'topic',
        'subject',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic] ?? $this->topic;
    }
}
