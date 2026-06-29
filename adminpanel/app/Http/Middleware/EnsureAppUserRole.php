<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class EnsureAppUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return $next($request);
    }
}
