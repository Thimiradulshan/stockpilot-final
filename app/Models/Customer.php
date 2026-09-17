<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'phone',
    'email',
    'address',
    'status',
])]
class Customer extends Model
{

use HasFactory;


public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }


public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
