<?php

namespace App\Domains\Auth\Http\Middleware;

use App\Domains\TutorProfile\Models\StudentProfile;
use Closure;
use Illuminate\Http\Request;

class EnsureTutorApproval
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->role !== 'admin' && $user->current_role !== 'admin') {
            // ป้องกันบัญชีเก่าหรือ Session เก่าข้ามขั้นตอนอนุมัติ
            if ($user->current_role === 'tutor' && $user->tutorProfile()->first()?->approval_status !== 'approved') {
                StudentProfile::firstOrCreate(['user_id' => $user->user_id]);
                $user->update(['current_role' => 'student']);
            }
            // Navbar ต้องตรงกับบทบาทจริงในฐานข้อมูลเสมอ
            $request->session()->put('active_role', $user->current_role);
        }

        return $next($request);
    }
}
