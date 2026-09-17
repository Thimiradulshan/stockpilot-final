<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'profile_photo_path'])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable
{

use HasFactory, Notifiable, TwoFactorAuthenticatable;


protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


public function roleEnum(): ?UserRole
    {
        $role = $this->getRawOriginal('role');

        if ($role instanceof UserRole) {
            return $role;
        }

        if (! is_string($role)) {
            return null;
        }

        return UserRole::tryFrom($role);
    }


public function purchases()
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }


public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }


public function payments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }


public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }


public function isAdmin(): bool
    {
        return $this->roleEnum() === UserRole::ADMIN;
    }


public function isSalesUser(): bool
    {
        return $this->roleEnum() === UserRole::SALES;
    }


public function isStockUser(): bool
    {
        return $this->roleEnum() === UserRole::STOCK;
    }


public function isActive(): bool
    {
        return $this->status === 'active';
    }


public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }


public function hasProfilePhoto(): bool
    {
        return is_string($this->profile_photo_path)
            && $this->profile_photo_path !== '';
    }


public function profilePhotoUrl(): ?string
    {
        if (! $this->hasProfilePhoto()) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }
}
