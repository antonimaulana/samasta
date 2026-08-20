<?php

namespace App\Policies;

use App\Models\User;
use App\Support\OperatorWilayahScope;
use Illuminate\Database\Eloquent\Model;

class BibitKeluarPolicy extends AdminContentPolicy
{
    public function delete(User $user, Model $model): bool
    {
        return $user->canWrite()
            && app(OperatorWilayahScope::class)->canAccess($user, $model);
    }
}
