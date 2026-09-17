<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->can('create', Product::class);
    }


public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('status', 'active')
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:200',
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],
            'cost_price' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'reorder_level' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,3',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
