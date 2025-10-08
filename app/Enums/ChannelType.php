<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'department';
    case INSTITUTE   = 'institute';
    case SECRETARY = 'secretary';
    case CENTER = 'center';
}
