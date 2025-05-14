<?php

namespace App\Enums;

enum ChangeRequestStatus: string
{
    /** The host has approved the change. */
    case PENDING = 'pending';

    /** The host rejected the change. */
    case REJECTED = 'rejected';

    /** The guest canceled the change. */
    case CANCELLED = 'cancelled';

    /** The change request has expired. */
    case EXPIRED = 'expired';

    /** The change has been approved. */
    case APPROVED = 'approved';
}
