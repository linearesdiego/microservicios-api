<?php

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';


    public static function values(): array
    {
        return array_map(fn(PostStatus $status) => $status->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::SCHEDULED => 'Programado',
            self::PUBLISHING => 'Publicando',
            self::PUBLISHED => 'Publicado',
            self::ARCHIVED => 'Archivado',
        };
    }
}
