<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->can('create', Category::class);
    }


public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'description' => 'description',
            'status' => 'status',
        ];
    }
}
