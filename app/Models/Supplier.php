<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'company',
    'phone',
    'email',
    'address',
    'status',
])]
class Supplier extends Model
{

use HasFactory;


public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'supplier_product_code',
                'last_cost',
                'is_preferred',
            ])
            ->withTimestamps();
    }


public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }


public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
