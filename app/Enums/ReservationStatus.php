<?php

namespace App\Enums;

enum ReservationStatus: string
{
    /** The guest asked to make a reservation. */
    case QUOTE = 'quote';

    /** The host has pre-approved the reservation. */
    case PENDING = 'pending';

    /** The guest has paid for the reservation. */
    case CONFIRMED = 'confirmed';

    /** The host rejected the reservation. */
    case REJECTED = 'rejected';

    /** The guest canceled the reservation. */
    case CANCELLED = 'cancelled';

    /** The reservation is completed. */
    case COMPLETED = 'completed';
}
