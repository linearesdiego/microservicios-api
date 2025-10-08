<?php

namespace App\Enums;

enum PostType: string
{
    case TEXT = 'text';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case IMAGE = 'image';
    case MULTIMEDIA = 'multimedia';
}
