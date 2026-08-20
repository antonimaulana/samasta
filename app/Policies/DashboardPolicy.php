<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function export(User $user): bool
    {
        return array_key_exists($user->resolvedRole(), User::ROLES);
    }
}
