<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\MediaType;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'configuration',
        'semantic_context',
        'is_active',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'configuration' => 'array',        // JSON se convierte a array automáticamente
        'is_active' => 'boolean',
    ];

    // ================================
    // RELACIONES
    // ================================

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_medias');
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_medias');
    }
}
