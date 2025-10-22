<?php

namespace App\Enums;

enum PostType: string
{
    case ARTICLE = 'article';
    case VIDEO = 'video';
    case PODCAST = 'podcast';
    case IMAGE = 'image';


    public static function values(): array
    {
        return array_map(fn(PostType $type) => $type->value, self::cases());
    }


    public function label(): string
    {
        return match ($this) {
            self::ARTICLE => 'Artículo',
            self::VIDEO => 'Video',
            self::PODCAST => 'Podcast',
            self::IMAGE => 'Imagen',
        };
    }
}

