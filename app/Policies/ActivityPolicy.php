<?php

namespace App\Policies;

use App\Models\User;

class ActivityPolicy
{
    /**
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isHost();
    }
}
