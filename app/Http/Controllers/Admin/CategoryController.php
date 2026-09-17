<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();

        Category::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        return to_route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    
    public function update(
        UpdateCategoryRequest $request,
        Category $category,
    ): RedirectResponse {
        $this->authorize('update', $category);

        $data = $request->validated();

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        return to_route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }
}
