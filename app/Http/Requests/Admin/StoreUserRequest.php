<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{

public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->can('create', User::class);
    }


public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'role' => [
                'required',
                Rule::enum(UserRole::class),
            ],
            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }


public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'role' => 'role',
            'status' => 'status',
            'password' => 'password',
        ];
    }
}
