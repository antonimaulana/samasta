<?php

namespace App\Policies;

use App\Models\User;
use App\Support\OperatorWilayahScope;
use Illuminate\Database\Eloquent\Model;

class PemeliharaanTamanPolicy extends AdminContentPolicy
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

    public function delete(User $user, Model $model): bool
    {
        return $user->canWrite()
            && app(OperatorWilayahScope::class)->canAccess($user, $model);
    }
}
