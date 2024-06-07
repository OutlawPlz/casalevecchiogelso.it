<?php

namespace App\Enums;

enum UserRole: string
{
    case GUEST = 'guest';
    case HOST = 'host';
}
