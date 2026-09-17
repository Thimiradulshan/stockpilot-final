<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{

public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->forceFill([
            'role' => $data['role'],
            'status' => $data['status'],
        ])->save();

        return to_route('admin.users.index')
            ->with('success', 'User created successfully.');
    }


public function update(
        UpdateUserRequest $request,
        User $user,
    ): RedirectResponse {
        $this->authorize('update', $user);

        $data = $request->validated();

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return to_route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
}
