<?php

namespace App\Enums;

enum ChangeRequestStatus: string
{
    /** The guest asked to change a reservation. */
    case DRAFT = 'draft';

    /** The host has approved the change. */
    case PENDING = 'pending';

    /** The host rejected the change. */
    case REJECTED = 'rejected';

    /** The guest canceled the change. */
    case CANCELLED = 'cancelled';

    /** The change is completed. */
    case CONFIRMED = 'confirmed';
}
