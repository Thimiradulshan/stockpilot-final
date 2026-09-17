<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function view(User $user, Supplier $supplier): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function update(User $user, Supplier $supplier): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function delete(User $user, Supplier $supplier): bool
    {
        return false;
    }


public function restore(User $user, Supplier $supplier): bool
    {
        return false;
    }


public function forceDelete(User $user, Supplier $supplier): bool
    {
        return false;
    }
}
