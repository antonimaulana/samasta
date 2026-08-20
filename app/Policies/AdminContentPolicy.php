<?php

namespace App\Policies;

use App\Models\User;
use App\Support\OperatorWilayahScope;
use Illuminate\Database\Eloquent\Model;

abstract class AdminContentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return app(OperatorWilayahScope::class)->canAccess($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->canWrite()
            && app(OperatorWilayahScope::class)->canAccess($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->canDelete()
            && app(OperatorWilayahScope::class)->canAccess($user, $model);
    }
}
