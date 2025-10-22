<?php

namespace App\Enums;

enum MediaType: string
{
    case PHISICAL_SCREEN = 'phisical_screen';
    case SOCIAL_MEDIA = 'social_media';
    case EDITORIAL_PLATFORM = 'editorial_platform';


    public static function values(): array
    {
        return array_map(fn(MediaType $type) => $type->value, self::cases());
    }


    public function label(): string
    {
        return match ($this) {
            self::PHISICAL_SCREEN => 'Pantalla física',
            self::SOCIAL_MEDIA => 'Red social',
            self::EDITORIAL_PLATFORM => 'Plataforma editorial',
        };
    }
}
