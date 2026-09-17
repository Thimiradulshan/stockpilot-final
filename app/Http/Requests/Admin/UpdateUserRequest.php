<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{

public function authorize(): bool
    {
        $user = $this->user();
        $target = $this->route('user');

        return $user !== null
            && $target instanceof User
            && $user->can('update', $target);
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
                Rule::unique('users', 'email')
                    ->ignore($this->route('user')),
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
                'nullable',
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
