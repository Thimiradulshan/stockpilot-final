<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{

public function authorize(): bool
    {
        return $this->user() !== null;
    }


public function rules(): array
    {
        return [
            'invoice_number' => [
                'nullable',
                'string',
                'max:50',
                'unique:invoices,invoice_number',
            ],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists(Customer::class, 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'status',
                            'active'
                        )
                    ),
            ],

            'invoice_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'
                    .now()
                        ->subDays(
                            (int) config(
                                'stockpilot.document_date_lookback_days'
                            )
                        )
                        ->toDateString(),
            ],

            'discount_type' => [
                'nullable',
                'string',
                Rule::in(['fixed', 'percent']),
            ],

            'discount_amount' => [
                'required',
                'decimal:0,2',
                'min:0',
                'max:999999999999.99',
            ],

            'payment_method' => [
                'nullable',
                'string',
                Rule::in([
                    'cash',
                    'card',
                    'bank_transfer',
                    'credit',
                ]),
            ],

            'amount_received' => [
                'nullable',
                'decimal:0,2',
                'min:0',
                'max:999999999999.99',
            ],

            'tax_rate' => [
                'required',
                'decimal:0,2',
                'min:0',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*' => [
                'required',
                'array',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Product::class, 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'status',
                            'active'
                        )
                    ),
            ],

            'items.*.quantity' => [
                'required',
                'decimal:0,3',
                'gt:0',
                'max:999999999999.999',
            ],

            'items.*.unit_price' => [
                'required',
                'decimal:0,2',
                'gt:0',
                'max:999999999999.99',
            ],

            'items.*.discount_amount' => [
                'nullable',
                'decimal:0,2',
                'min:0',
                'max:999999999999.99',
            ],

            'items.*.tax_amount' => [
                'nullable',
                'decimal:0,2',
                'min:0',
                'max:999999999999.99',
            ],
        ];
    }


protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_array($items)) {
            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $items[$index]['discount_amount']
                    = $item['discount_amount'] ?? '0';

                $items[$index]['tax_amount']
                    = $item['tax_amount'] ?? '0';
            }

            $this->merge([
                'items' => $items,
            ]);
        }
    }
}
