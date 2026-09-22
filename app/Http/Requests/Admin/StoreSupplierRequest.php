<?php

namespace App\Http\Requests\Admin;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{

public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->can('create', Supplier::class);
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
                'regex:/^(?:\+94|0)7\d{8}$/',
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


public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid mobile number, e.g. 0712345678 or +94712345678.',
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
