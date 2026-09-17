<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{

public function authorize(): bool
    {
        return $this->user() !== null;
    }


public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'decimal:0,2',
                'gt:0',
                'max:999999999999.99',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:30',
                Rule::in(['cash', 'card', 'bank_transfer', 'credit']),
            ],

            'idempotency_key' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}
