<?php

namespace App\Models;

use App\Notifications\ProfessionalResetPasswordNotification;
use App\Notifications\ProfessionalVerifyEmailNotification;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_member',
        'is_health_professional',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_member' => 'boolean',
            'is_health_professional' => 'boolean',
        ];
    }

    /**
     * Seuls les administrateurs accèdent au panneau Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ProfessionalVerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ProfessionalResetPasswordNotification($token));
    }
}
