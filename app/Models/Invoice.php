<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'customer_id',
    'invoice_date',
    'subtotal',
    'discount_amount',
    'tax_rate',
    'tax_amount',
    'total_amount',
    'status',
    'payment_status',
    'notes',
    'created_by',
])]
class Invoice extends Model
{

use HasFactory;


public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }


public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }


protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }
}
