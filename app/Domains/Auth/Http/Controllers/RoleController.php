<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\TutorProfile\Models\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function switchRole(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:tutor,student'],
        ]);

        $user = $request->user();
        $role = $validated['role'];

        if ($role === 'tutor' && ! $user->tutorProfile?->isComplete()) {
            return redirect()->route('tutor.onboarding')->with('info', 'กรุณากรอกข้อมูลติวเตอร์ก่อนเริ่มสอน');
        }

        if ($role === 'tutor' && $user->tutorProfile->approval_status !== 'approved') {
            return redirect()->route('tutor.application.status');
        }

        if ($role === 'student') {
            StudentProfile::firstOrCreate([
                'user_id' => $user->user_id,
            ], [
                'id' => strtoupper(Str::random(10)),
            ]);
            $user->load('studentProfile');
        }

        $user->current_role = $role;
        $user->save();
        $user->refresh();

        return redirect()->route($role === 'tutor' ? 'tutor.profile' : 'home')->with(
            'success',
            'Switched to '.ucfirst($role).' mode.'
        );
    }
}
