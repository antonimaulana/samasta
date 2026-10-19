<?php

namespace App\Policies;

use App\Models\User;
use App\Support\OperatorWilayahScope;
use Illuminate\Database\Eloquent\Model;

class PemangkasanPolicy extends AdminContentPolicy
{
    public function create(User $user): bool
    {
        return $user->canInputLapangan();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->canInputLapangan()
            && app(OperatorWilayahScope::class)->canAccess($user, $model);
    }
}
