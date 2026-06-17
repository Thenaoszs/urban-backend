<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_blocked'        => 'boolean',
            'date_creation'     => 'datetime',
        ];
    }

    /**
     * Append custom attributes.
     */
    protected $appends = ['date_creation'];

    /**
     * Relation : un utilisateur a plusieurs signalements.
     */
    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class, 'utilisateur_id');
    }

    // ── Helpers rôles ────────────────────────────────────────────────────────

    public function isCitoyen(): bool
    {
        return $this->role === 'citoyen';
    }

    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    // ── Accesseurs ───────────────────────────────────────────────────────────

    /**
     * Expose created_at as date_creation for consistency with the CDC.
     */
    public function getDateCreationAttribute(): ?string
    {
        return $this->created_at?->toIso8601String();
    }
}
