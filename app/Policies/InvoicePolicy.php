<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{

public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function view(User $user, Invoice $invoice): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser());
    }


public function void(User $user, Invoice $invoice): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser())
            && $invoice->status === 'completed'
            && $invoice->payment_status === 'unpaid';
    }


public function pay(User $user, Invoice $invoice): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isSalesUser())
            && $invoice->status === 'completed';
    }


public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }


public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }


public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }


public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
