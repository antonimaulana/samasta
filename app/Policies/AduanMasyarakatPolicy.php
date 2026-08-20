<?php

namespace App\Policies;

use App\Models\AduanMasyarakat;
use App\Models\User;

class AduanMasyarakatPolicy extends AdminContentPolicy
{
    /**
     * @param  AduanMasyarakat  $model
     */
    public function updateStatus(User $user, AduanMasyarakat $model): bool
    {
        return $user->canWrite();
    }
}
