<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function view(User $user, Product $product): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function update(User $user, Product $product): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function adjustStock(User $user, Product $product): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isStockUser());
    }


public function delete(User $user, Product $product): bool
    {
        return false;
    }


public function restore(User $user, Product $product): bool
    {
        return false;
    }


public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
