<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $category = $this->route('category');

        return $user !== null
            && $category instanceof Category
            && $user->can('update', $category);
    }


public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore(
                    $category instanceof Category
                        ? $category->getKey()
                        : null
                ),
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
