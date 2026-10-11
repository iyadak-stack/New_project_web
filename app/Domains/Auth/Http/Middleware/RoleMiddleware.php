<?php

namespace App\Domains\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->current_role !== $role) {
            return redirect()
                ->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }

        if ($role === 'tutor' && $user->tutorProfile?->approval_status !== 'approved') {
            return redirect()->route('tutor.application.status');
        }

        return $next($request);
    }
}
