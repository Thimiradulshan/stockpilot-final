<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{

public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        if (! $user->isActive()) {
            abort(403);
        }

        $userRole = $user->roleEnum();

        if ($userRole === null) {
            abort(403);
        }

        $allowedRoles = collect($roles)
            ->map(
                fn (string $role): ?UserRole => UserRole::tryFrom($role)
            )
            ->filter()
            ->values();

        if ($allowedRoles->isEmpty()) {
            abort(403);
        }


        if ($userRole === UserRole::ADMIN) {
            return $next($request);
        }

        if (! $allowedRoles->contains($userRole)) {
            abort(403);
        }

        return $next($request);
    }
}
