<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorSearchController extends Controller
{
    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        // หน้ารวมแสดงเฉพาะผู้ที่อนุมัติและบัญชียังเปิดใช้งาน
        $tutorQuery = TutorProfile::approved()->forStudentList()
            ->with(['user', 'subjects'])
            ->withCount('reviews');

        // ค้นหาจากชื่อผู้สอนหรือชื่อวิชา โดยรวมเงื่อนไขไว้ในกลุ่มเดียวกัน
        if ($search !== '') {
            $tutorQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    // users เก็บชื่อและนามสกุล ไม่มีคอลัมน์ username
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('subjects', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('subject_id', 'like', "%{$search}%")
                            ->orWhere('subject_name', 'like', "%{$search}%");
                    });
            });
        }

        $tutors = $tutorQuery->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        $subjectQuery = Subject::with(['tutors' => function ($query) {
            $query->approved()->forStudentList()->with('user');
        }]);

        if ($search !== '') {
            $subjectQuery->where('subject_id', 'like', "%{$search}%")
                ->orWhere('subject_name', 'like', "%{$search}%");
        }

        $subjects = $subjectQuery->get();

        return view('domains.tutor-profile.tutor.search', compact('tutors', 'subjects', 'search'));
    }

    public function ranking()
    {
        $tutors = TutorProfile::approved()->forStudentList()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        return view('domains.tutor-profile.tutor.ranking', compact('tutors'));
    }

    public function show($tutor)
    {
        if ($tutor instanceof TutorProfile) {
            $tutorProfile = $tutor;
        } else {
            $tutorProfile = TutorProfile::where('tutor_id', $tutor)
                ->orWhere('Users_user_id', $tutor)
                ->firstOrFail();
        }

        $isApproved = $tutorProfile->approval_status === 'approved';

        abort_unless($isApproved || $tutorProfile->Users_user_id == Auth::id(), 404);

        $tutorProfile->load(['user', 'subjects', 'reviews', 'appointments']);

        $isFavorite = false;
        if (Auth::check()) {
            $isFavorite = Favorite::where('Users_user_id', Auth::id())
                ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
                ->exists();
        }

        $allSubjects = Subject::all();

        return view('domains.tutor-profile.tutor.show', compact('tutorProfile', 'isFavorite', 'allSubjects'));
    }
}
