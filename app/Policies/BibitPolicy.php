<?php

namespace App\Policies;

use App\Models\User;

class BibitPolicy extends AdminContentPolicy
{
    public function import(User $user): bool
    {
        return $user->canWrite();
    }
}
