<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
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

        if ($role === 'tutor') {
            // สร้าง TutorProfile อัตโนมัติหากยังไม่มีใน Database
            TutorProfile::firstOrCreate(
                ['Users_user_id' => $user->user_id],
                [
                    'tutor_id' => 'TUT-' . strtoupper(Str::random(8)),
                    'bio' => '',
                    'experience_years' => 0,
                    'total_teaching_seconds' => 0,
                    'average_rating' => 0,
                    'teaching_mode' => 'online',
                ]
            );
            $user->load('tutorProfile');
        }

        if ($role === 'student') {
            StudentProfile::firstOrCreate([
                'user_id' => $user->user_id,
            ]);
            $user->load('studentProfile');
        }

        $user->current_role = $role;
        $user->save();
        $user->refresh();

        return back()->with(
            'success',
            'Switched to ' . ucfirst($role) . ' mode.'
        );
    }
}