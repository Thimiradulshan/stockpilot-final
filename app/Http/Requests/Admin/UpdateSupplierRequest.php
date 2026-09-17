<?php

namespace App\Http\Requests\Admin;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{

public function authorize(): bool
    {
        $user = $this->user();
        $supplier = $this->route('supplier');

        return $user !== null
            && $supplier instanceof Supplier
            && $user->can('update', $supplier);
    }


public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'company' => [
                'nullable',
                'string',
                'max:150',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }


public function attributes(): array
    {
        return [
            'name' => 'supplier name',
            'company' => 'company',
            'phone' => 'phone number',
            'email' => 'email address',
            'address' => 'address',
            'status' => 'status',
        ];
    }
}
