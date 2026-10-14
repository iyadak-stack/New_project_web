<?php

namespace App\Domains\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // ตรวจจากบัญชีใน DB ไม่เชื่อ active_role ที่อยู่ใน Session
        abort_unless($user && ($user->role === 'admin' || $user->current_role === 'admin'), 403);

        return $next($request);
    }
}
