<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ChannelType;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'semantic_context',
        'type',
        'is_active',
    ];

    protected $casts = [
        'type' => ChannelType::class,
        'is_active' => 'boolean',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Un canal puede tener muchos usuarios (relación N:M)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_channels')
            ->withPivot(['is_approved', 'approved_at', 'approved_by']);
    }

    /**
     * Solo usuarios aprobados del canal
     */
    public function approvedUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_approved', true);
    }

    /**
     * Un canal puede tener muchos posts (relación N:M)
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_channels');
    }

    /**
     * Solo posts publicados del canal
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->where('status', 'published');
    }

    /**
     * Un canal puede usar muchos medios (relación N:M)
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'channel_medias');
    }
}

