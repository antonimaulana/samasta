<?php

namespace App\Policies;

use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return array_key_exists($user->resolvedRole(), User::ROLES);
    }

    public function update(User $user): bool
    {
        return $user->canWrite();
    }
}
