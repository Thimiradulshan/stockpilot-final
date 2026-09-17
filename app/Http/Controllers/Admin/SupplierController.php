<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupplierRequest;
use App\Http\Requests\Admin\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;

class SupplierController extends Controller
{

public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $request->validated();

        Supplier::query()->create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        return to_route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }


public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier,
    ): RedirectResponse {
        $this->authorize('update', $supplier);

        $data = $request->validated();

        $supplier->update([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        return to_route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }
}
