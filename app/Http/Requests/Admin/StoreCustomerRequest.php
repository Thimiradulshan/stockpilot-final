<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{

public function authorize(): bool
    {
        return $this->user()?->can('create', Customer::class) ?? false;
    }


public function rules(): array
    {
        return [
            'name' => [
                'required',
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
                'in:active,inactive',
            ],
        ];
    }


public function messages(): array
    {
        return [
            'name.required' => 'Customer name is required.',
            'name.max' => 'Customer name may not exceed 150 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email may not exceed 150 characters.',
            'phone.max' => 'Phone number may not exceed 30 characters.',
            'address.max' => 'Address may not exceed 5000 characters.',
            'status.in' => 'Customer status must be active or inactive.',
        ];
    }
}
