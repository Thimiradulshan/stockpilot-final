<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'status'])]
class Category extends Model
{

use HasFactory;


public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
