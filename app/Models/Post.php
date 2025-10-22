<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\PostType;
use App\Enums\PostStatus;
use Illuminate\Mail\Attachment;

class Post extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'user_id',
        'name',
        'content',
        'type',
        'status',
        'moderator_comments',
        'scheduled_at',
        'published_at',
        'deadline',
        'timeout',
    ];

    /**
     * Conversión automática de tipos de datos
     */
    protected $casts = [
        'type' => PostType::class,
        'status' => PostStatus::class,
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'deadline' => 'datetime',
        'timeout' => 'datetime',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Un post pertenece a un usuario (relación 1:N inversa)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un post puede estar en muchos canales (relación N:M)
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'post_channels');
    }

    /**
     * Un post puede usar muchos medios (relación N:M)
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_medias');
    }

    /**
     * Un post tiene muchos archivos adjuntos (relación 1:N)
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
