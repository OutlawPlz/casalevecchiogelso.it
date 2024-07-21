<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case QUOTE = 'quote';
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';
}
