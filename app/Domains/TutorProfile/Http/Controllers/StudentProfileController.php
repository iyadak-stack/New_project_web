<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\StudentProfile;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function profile()
    {
        $studentProfile = StudentProfile::where('user_id', auth()->id())->first();
        return view('domains.tutor-profile.student.profile', compact('studentProfile'));
    }

    public function editProfile()
    {
        $studentProfile = StudentProfile::where('user_id', auth()->id())->first();
        return view('domains.tutor-profile.student.edit-profile', compact('studentProfile'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'bio' => ['nullable', 'string'],
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'bio' => $request->bio,
            ]
        );

        return redirect()
            ->route('student.profile')
            ->with('success', 'Student profile updated successfully.');
    }
}