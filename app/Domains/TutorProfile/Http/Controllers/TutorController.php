<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Auth\Models\Contact;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Services\TutorApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorController extends Controller
{
    /**
     * ชื่อ view หน้าแรกของนักเรียนที่ล็อกอินแล้ว (ไฟล์ student.blade.php)
     */
    private const STUDENT_HOME_VIEW = 'domains.tutor-profile.student.student';

    public function home(Request $request)
    {
        // ดึงติวเตอร์ทั้งหมดมาแสดง
        $tutors = TutorProfile::approved()->with(['user', 'subjects'])
            ->when(Auth::check(), fn ($query) => $query->where('Users_user_id', '!=', Auth::id())
            )
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
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

    public function profile()
    {
        $profile = Auth::user()->tutorProfile;
        if (! $profile || ! $profile->isComplete()) {
            return redirect()->route('tutor.onboarding');
        }
        if ($profile->approval_status !== 'approved') {
            return redirect()->route('tutor.application.status');
        }
        $appointments = $profile->appointments()
            ->with(['subject', 'studentUser', 'studentProfile.user'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_datetime')
            ->get();
        $reviews = $profile->reviews()->latest()->get();
        $availabilities = Availability::where('user_id', Auth::id())
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime')
            ->get();

        return view('domains.tutor-profile.tutor.dashboard', compact('profile', 'appointments', 'reviews', 'availabilities'));
    }

    public function applicationStatus()
    {
        $profile = Auth::user()->tutorProfile;
        if (! $profile) {
            return redirect()->route('tutor.onboarding');
        }

        return view('domains.tutor-profile.tutor.application-status', compact('profile'));
    }

    public function editProfile()
    {
        $tutorProfile = TutorProfile::firstOrNew(['Users_user_id' => Auth::id()]);
        $allSubjects = Subject::all();
        $contact = Contact::where('Users_user_id', Auth::id())->first();

        return view(
            'domains.tutor-profile.tutor.edit-profile',
            compact('tutorProfile', 'allSubjects', 'contact')
        );
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate(TutorApplication::rules());
        $profile = TutorApplication::save($request->user(), $data);
        if ($profile->approval_status !== 'approved') {
            return redirect()->route('tutor.application.status')->with('success', 'ส่งข้อมูลแล้ว กรุณารอแอดมินตรวจสอบ');
        }

        return redirect()->route('tutor.profile')->with('success', 'บันทึกข้อมูลแล้ว');
    }

    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tutors = TutorProfile::approved()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('subjects', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('subject_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        $subjects = $tutors
            ->flatMap(function ($tutor) {
                return $tutor->subjects;
            })
            ->unique('subject_id')
            ->values();

        if ($search !== '') {
            $matchedSubjects = Subject::with('tutors.user')
                ->where('subject_name', 'like', "%{$search}%")
                ->get();

            $subjects = $subjects
                ->merge($matchedSubjects)
                ->unique('subject_id')
                ->values();
        }

        return view(
            'domains.tutor-profile.tutor.search',
            compact('tutors', 'subjects', 'search')
        );
    }

    public function ranking()
    {
        $tutors = TutorProfile::approved()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        return view('domains.tutor-profile.tutor.ranking', compact('tutors'));
    }

    public function show($tutor)
    {
        // ค้นหาติวเตอร์จาก tutor_id หรือ id หรือผ่าน Route Model Binding
        if ($tutor instanceof TutorProfile) {
            $tutorProfile = $tutor;
        } else {
            $tutorProfile = TutorProfile::where('tutor_id', $tutor)
                ->orWhere('Users_user_id', $tutor)
                ->firstOrFail();
        }

        // ผู้ใช้ทั่วไปดูได้เฉพาะติวเตอร์ที่อนุมัติแล้ว เจ้าของยังดูโปรไฟล์ตัวเองได้
        $isApproved = $tutorProfile->approval_status === 'approved' && $tutorProfile->user?->is_active;

        abort_unless(
            $isApproved || $tutorProfile->Users_user_id == Auth::id(),
            404
        );

        $tutorProfile->load([
            'user',
            'subjects',
            'reviews',
            'appointments',
        ]);

        $isFavorite = false;
        if (Auth::check()) {
            $isFavorite = Favorite::where('Users_user_id', Auth::id())
                ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
                ->exists();
        }

        // 🟢 ดึงข้อมูลวิชาทั้งหมดจาก Seeder/DB สำรองไว้กรณีติวเตอร์ยังไม่ได้เลือกผูกวิชา
        $allSubjects = Subject::all();

        $bookedAppointments = Appointment::query()
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('start_datetime')
            ->whereNotNull('end_datetime')
            ->get(['start_datetime', 'end_datetime']);

        $availableSlots = Availability::query()
            ->where('user_id', $tutorProfile->Users_user_id)
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime')
            ->get();

        // แสดงเฉพาะเวลาว่างที่ไม่ทับกับนัดหมายที่มีคนจองไว้แล้ว
        $availabilities = collect();
        foreach ($availableSlots as $slot) {
            $isBooked = false;
            foreach ($bookedAppointments as $appointment) {
                if ($appointment->start_datetime < $slot->end_datetime
                    && $appointment->end_datetime > $slot->start_datetime) {
                    $isBooked = true;
                    break;
                }
            }

            if (! $isBooked) {
                $availabilities->push($slot);
            }
        }

        return view(
            'domains.tutor-profile.tutor.show',
            compact('tutorProfile', 'isFavorite', 'allSubjects', 'availabilities')
        );
    }
}
