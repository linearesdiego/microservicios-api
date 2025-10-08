<?php

namespace App\Enums;

enum PostStatus : string
{
    case DRAFT = 'draft';
    case APPROVED_BY_MODERATOR = 'approved_by_moderator';
    case SCHEDULED = 'scheduled';
    case ARCHIVED = 'archived';
}
