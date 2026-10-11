<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Booking\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentHomeController extends Controller
{
    private const STUDENT_HOME_VIEW = 'domains.tutor-profile.student.student';
    private const ALL_SUBJECTS_VIEW = 'domains.booking.subjects.subjects';
    private const ALL_TUTORS_VIEW   = 'domains.tutor-profile.tutor.tutors';

    /**
     * แสดงหน้าแรกของนักเรียน/ต้อนรับผู้ใช้
     */
    public function index(Request $request)
    {
        $tutors = TutorProfile::approved()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->take(3)
            ->get();

        $subjects = Subject::withCount('tutors')
            ->orderByDesc('tutors_count')
            ->take(3)
            ->get();

        if (Auth::check()) {
            return view(self::STUDENT_HOME_VIEW, compact('tutors', 'subjects'));
        }

        return view('welcome', compact('tutors', 'subjects'));
    }

    /**
     * แสดงหน้ารายวิชาทั้งหมดโดยเฉพาะ
     */
    public function allSubjects()
    {
        $subjects = Subject::withCount('tutors')
            ->orderByDesc('tutors_count')
            ->get();

        return view(self::ALL_SUBJECTS_VIEW, compact('subjects'));
    }

    /**
     * แสดงหน้าติวเตอร์ทั้งหมดสำหรับนักเรียน (รองรับค้นหาด้วย ?search=)
     * route: student.tutors.index
     */
    public function allTutors(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $tutors = TutorProfile::approved()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($u) use ($search) {
                        $u->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('subjects', function ($s) use ($search) {
                        $s->where('subject_name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        return view(self::ALL_TUTORS_VIEW, compact('tutors'));
    }
}
