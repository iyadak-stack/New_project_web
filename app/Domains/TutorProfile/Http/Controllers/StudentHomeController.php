<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentHomeController extends Controller
{
    private const STUDENT_HOME_VIEW = 'domains.tutor-profile.student.student';

    private const ALL_SUBJECTS_VIEW = 'domains.booking.subjects.subjects';

    private const ALL_TUTORS_VIEW = 'domains.tutor-profile.tutor.tutors';

    /**
     * แสดงหน้าแรกของนักเรียน/ต้อนรับผู้ใช้
     */
    public function index(Request $request)
    {
        $tutors = TutorProfile::approved()->forStudentList()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->take(3)
            ->get();

        $subjects = Subject::withCount(['tutors' => function ($query) {
            $query->approved()->forStudentList();
        }])
            ->orderByDesc('tutors_count')
            ->take(3)
            ->get();

        if (Auth::check()) {
            // แสดงเฉพาะนัดหมายที่ยืนยันแล้วของนักเรียนคนนี้
            $studentId = $request->user()->studentProfile?->id;
            $confirmedAppointments = Appointment::with(['subject', 'tutorProfile.user'])
                // รองรับทั้งนัดหมายเก่าที่เก็บรหัสโปรไฟล์ และของเพื่อนที่เก็บรหัสผู้ใช้
                ->whereIn('Student_profiles_student_id', [$studentId, $request->user()->user_id])
                ->where('status', 'confirmed')
                ->where('end_datetime', '>=', now('Asia/Bangkok')->format('Y-m-d H:i:s'))
                ->orderBy('start_datetime')->take(3)->get();

            return view(self::STUDENT_HOME_VIEW, compact('tutors', 'subjects', 'confirmedAppointments'));
        }

        return view('welcome', compact('tutors', 'subjects'));
    }

    /**
     * แสดงหน้ารายวิชาทั้งหมดโดยเฉพาะ
     */
    public function allSubjects()
    {
        $subjects = Subject::withCount(['tutors' => function ($query) {
            $query->approved()->forStudentList();
        }])
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

        // แสดงเฉพาะผู้ที่อนุมัติ และยังซ่อนตัวเองเมื่อใช้บทบาทนักเรียน
        $tutors = TutorProfile::approved()->forStudentList()->with(['user', 'subjects'])
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
