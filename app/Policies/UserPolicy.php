<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }


public function view(User $user, User $model): bool
    {
        return $user->isActive() && $user->isAdmin();
    }


public function create(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }


public function update(User $user, User $model): bool
    {
        if (! $user->isActive() || ! $user->isAdmin()) {
            return false;
        }


        return $user->isNot($model);
    }


public function delete(User $user, User $model): bool
    {
        return false;
    }


public function restore(User $user, User $model): bool
    {
        return false;
    }


public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
