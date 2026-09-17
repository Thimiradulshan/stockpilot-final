<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'name',
    'sku',
    'cost_price',
    'selling_price',
    'reorder_level',
    'description',
    'status',
])]
class Product extends Model
{

use HasFactory;


public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot([
                'supplier_product_code',
                'last_cost',
                'is_preferred',
            ])
            ->withTimestamps();
    }


public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }


public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }


public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }


protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'reorder_level' => 'decimal:3',
        ];
    }


public function isActive(): bool
    {
        return $this->status === 'active';
    }


public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }


public function hasStock(): bool
    {
        return $this->quantity > 0;
    }
}
