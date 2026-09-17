<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{

public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validated();

        Customer::query()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        return to_route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }


public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ): RedirectResponse {
        $this->authorize('update', $customer);

        $validated = $request->validated();

        $customer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        return to_route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }
}
