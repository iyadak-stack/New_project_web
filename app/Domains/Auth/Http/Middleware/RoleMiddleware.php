<?php

namespace App\Domains\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (auth()->user()->current_role !== $role) {
            return redirect()
                ->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}