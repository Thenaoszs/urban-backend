<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Signalement extends Model
{
    use HasFactory;

    // Valeurs autorisées pour le type
    const TYPES = [
        'inondation',
        'chaussee',
        'electricite',
        'eau',
        'dechets',
        'foret',
    ];

    // Valeurs autorisées pour le statut
    const STATUTS = [
        'en_cours',
        'accepte',
        'rejete',
        'traite',
    ];

    protected $fillable = [
        'utilisateur_id',
        'type',
        'description',
        'latitude',
        'longitude',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'latitude'      => 'float',
            'longitude'     => 'float',
            'date_creation' => 'datetime',
        ];
    }

    protected $appends = ['date_creation'];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImageSignalement::class, 'signalement_id');
    }

    // ── Accesseurs ────────────────────────────────────────────────────────────

    public function getDateCreationAttribute(): ?string
    {
        return $this->created_at?->toIso8601String();
    }
}
