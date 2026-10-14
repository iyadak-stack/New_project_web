<?php

namespace App\Domains\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        // บัญชีที่ถูกพักต้องออกจากระบบด้วย แม้ล็อกอินค้างอยู่ก่อนถูกพัก
        if ($request->user() && ! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('error', 'บัญชีนี้ถูกพักการใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        return $next($request);
    }
}
