<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable([
    'product_id',
    'movement_type',
    'quantity',
    'quantity_before',
    'quantity_after',
    'reference_type',
    'reference_id',
    'reason',
    'notes',
    'created_by',
])]
class StockMovement extends Model
{

use HasFactory;


protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException(
                'Stock movements are immutable and cannot be updated.'
            );
        });

        static::deleting(function (): void {
            throw new LogicException(
                'Stock movements are immutable and cannot be deleted.'
            );
        });
    }


public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'quantity' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'reference_id' => 'integer',
        ];
    }
}
