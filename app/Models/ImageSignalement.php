<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ImageSignalement extends Model
{
    use HasFactory;

    protected $table = 'images_signalement';

    protected $fillable = [
        'signalement_id',
        'image_path',
    ];

    protected $appends = ['url'];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function signalement(): BelongsTo
    {
        return $this->belongsTo(Signalement::class, 'signalement_id');
    }

    // ── Accesseurs ────────────────────────────────────────────────────────────

    /**
     * URL publique complète vers l'image (via storage:link).
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }
}
