<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function view(User $user, Purchase $purchase): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function cancel(User $user, Purchase $purchase): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser())
            && $purchase->status === 'completed';
    }


public function update(User $user, Purchase $purchase): bool
    {
        return false;
    }


public function delete(User $user, Purchase $purchase): bool
    {
        return false;
    }


public function restore(User $user, Purchase $purchase): bool
    {
        return false;
    }


public function forceDelete(User $user, Purchase $purchase): bool
    {
        return false;
    }
}
