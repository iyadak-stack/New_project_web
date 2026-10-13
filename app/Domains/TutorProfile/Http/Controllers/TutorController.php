<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Auth\Models\Contact;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\Scheduling\Services\AvailabilityService;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Services\TutorApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TutorController extends Controller
{
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
        $rules = TutorApplication::rules();
        $rules['first_name'] = ['required', 'string', 'max:50'];
        $rules['last_name'] = ['required', 'string', 'max:50'];
        $data = $request->validate($rules);
        $profile = TutorApplication::save($request->user(), $data);
        $request->user()->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);
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

        /*
         * โค้ดเดิมของส่วน Tutor Profile (แฟง) ก่อนปกป้องเชื่อม Service
         * ปกป้องเชื่อมให้แฟง: โค้ดเดิมด้านล่างซ่อนทั้ง Availability เมื่อมีนัดทับบางส่วน
         * ถ้าแฟงเขียน Controller เอง เพิ่ม use App\Domains\Scheduling\Services\AvailabilityService;
         * แล้วเรียก findTutorAvailableTimes(user_id ของติวเตอร์) ได้เลย ไม่ต้องเขียนการตัดช่วงเวลาซ้ำ
         * ผลลัพธ์เป็น array แต่ละรายการมี start/end; Blade ใช้เวลาเหล่านี้ ไม่ใช่ availability_id
         * $bookedAppointments = Appointment::query()
         * ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
         * ->where('status', '!=', 'cancelled')
         * ->whereNotNull('start_datetime')
         * ->whereNotNull('end_datetime')
         * ->get(['start_datetime', 'end_datetime']);
         *
         * $availableSlots = Availability::query()
         * ->where('user_id', $tutorProfile->Users_user_id)
         * ->where('start_datetime', '>', now())
         * ->orderBy('start_datetime')
         * ->get();
         *
         * // แสดงเฉพาะเวลาว่างที่ไม่ทับกับนัดหมายที่มีคนจองไว้แล้ว
         * $availabilities = collect();
         * foreach ($availableSlots as $slot) {
         * $isBooked = false;
         * foreach ($bookedAppointments as $appointment) {
         * if ($appointment->start_datetime < $slot->end_datetime
         * && $appointment->end_datetime > $slot->start_datetime) {
         * $isBooked = true;
         * break;
         * }
         * }
         *
         * if (! $isBooked) {
         * $availabilities->push($slot);
         * }
         * }
         */
        // โค้ดใหม่ที่ปกป้องเพิ่มให้: แสดงเฉพาะช่วงที่เหลือ ไม่ซ่อนทั้งรายการ
        $availabilityService = app(AvailabilityService::class);
        $tutorUserId = (string) $tutorProfile->Users_user_id;
        $availableTimes = $availabilityService->findTutorAvailableTimes($tutorUserId);

        // ฝั่งจองแสดงเฉพาะช่วงที่ตรงกับเวลาว่างของคนที่ล็อกอินด้วย
        $bookableTimes = $availableTimes;
        if (Auth::check()) {
            $bookableTimes = [];
            if (Auth::user()->studentProfile) {
                foreach ($availableTimes as $time) {
                    $sharedTimes = $availabilityService->findSharedTimes(
                        (string) Auth::id(),
                        $tutorUserId,
                        Carbon::parse($time['start']),
                        Carbon::parse($time['end']),
                    );
                    $bookableTimes = array_merge($bookableTimes, $sharedTimes);
                }
            }
        }

        return view(
            'domains.tutor-profile.tutor.show',
            compact('tutorProfile', 'isFavorite', 'allSubjects', 'availableTimes', 'bookableTimes')
        );
    }
}
