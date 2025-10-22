<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'name',
        'mime_type',
        'size',
        'path',
        'url',
        'protected',
        'metadata',
    ];

    protected $casts = [
        'protected' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Un attachment pertenece a un post (relación 1:N inversa)
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
