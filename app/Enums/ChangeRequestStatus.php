<?php

namespace App\Enums;

enum ChangeRequestStatus: string
{
    case PENDING = 'pending';

    /** The change has been rejected. */
    case REJECTED = 'rejected';

    /** The change request creator has canceled it. */
    case CANCELLED = 'cancelled';

    /** The change request has expired. */
    case EXPIRED = 'expired';

    /** The change has been approved. */
    case APPROVED = 'approved';
}
