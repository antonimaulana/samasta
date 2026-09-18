<?php

namespace App\Policies;

use App\Models\Taman;
use App\Models\User;

class TamanPolicy extends AdminContentPolicy
{
    /**
     * @param  Taman  $model
     */
    public function deleteImage(User $user, Taman $model): bool
    {
        return $user->canWrite();
    }

    public function import(User $user): bool
    {
        return $user->canImportBulk();
    }
}
