<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $product = $this->route('product');

        return $user !== null
            && $product instanceof Product
            && $user->can('update', $product);
    }


public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(
                    function ($query) use ($product) {
                        $query->where(function ($categoryQuery) use ($product) {
                            $categoryQuery
                                ->where('status', 'active');

                            if ($product instanceof Product) {
                                $categoryQuery->orWhere(
                                    'id',
                                    $product->category_id
                                );
                            }
                        });
                    }
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
                Rule::unique('products', 'sku')->ignore(
                    $product instanceof Product ? $product->getKey() : null
                ),
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
