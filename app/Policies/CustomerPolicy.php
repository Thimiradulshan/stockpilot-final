<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function view(User $user, Customer $customer): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function update(User $user, Customer $customer): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function delete(User $user, Customer $customer): bool
    {
        return false;
    }


public function restore(User $user, Customer $customer): bool
    {
        return false;
    }


public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }
}
