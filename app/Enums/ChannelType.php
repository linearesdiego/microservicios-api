<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'department';
    case INSTITUTE = 'institute';
    case SECRETARY = 'secretary';
    case CENTER = 'center';

    public static function values(): array
    {
        return array_map(fn(ChannelType $type) => $type->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::DEPARTMENT => 'Departamento',
            self::INSTITUTE => 'Instituto',
            self::SECRETARY => 'Secretaría',
            self::CENTER => 'Centro',
        };
    }

}
