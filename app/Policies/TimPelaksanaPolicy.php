<?php

namespace App\Policies;

use App\Models\TimPelaksana;
use App\Models\User;

class TimPelaksanaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canWrite() || $user->isViewer();
    }

    public function manageWilayah(User $user, TimPelaksana $timPelaksana): bool
    {
        return $user->canManageUsers() && $timPelaksana->memiliki_wilayah_kerja;
    }
}
