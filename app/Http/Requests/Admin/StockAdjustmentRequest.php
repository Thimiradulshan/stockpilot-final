<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{

public function authorize(): bool
    {
        return $this->user() !== null;
    }


public function rules(): array
    {
        return [
            'signed_quantity' => [
                'required',
                'decimal:0,3',
                'not_in:0,0.0,0.00,0.000,-0,-0.0,-0.00,-0.000',
                'max:999999999999.999',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
