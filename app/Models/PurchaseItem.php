<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_id',
    'product_id',
    'quantity',
    'unit_cost',
    'discount_amount',
    'tax_amount',
    'line_total',
])]
class PurchaseItem extends Model
{

public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }


public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }
}
