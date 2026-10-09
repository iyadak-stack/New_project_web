<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Models\Contact;
use Illuminate\Support\Str;

class TutorController extends Controller
{
    /**
     * ชื่อ view หน้าแรกของนักเรียนที่ล็อกอินแล้ว (ไฟล์ student.blade.php)
     */
    private const STUDENT_HOME_VIEW = 'domains.tutor-profile.student.student';

    public function home(Request $request)
    {
        // ดึงติวเตอร์ทั้งหมดมาแสดง
        $tutors = TutorProfile::with(['user', 'subjects'])
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

    public function profile()
    {
        $user = Auth::user();

        // เช็กบทบาทปัจจุบันของผู้ใช้
        if ($user->current_role === 'tutor') {
            $tutorProfile = TutorProfile::where(
                'Users_user_id',
                $user->user_id
            )->first();

            $contact = Contact::where('Users_user_id', $user->user_id)->first();

            // ส่งไปยัง View Dashboard หน้าติวเตอร์
            return view(
                'domains.tutor-profile.tutor.tutor',
                compact('tutorProfile', 'user', 'contact')
            );
        }

        // กรณีเป็นนักเรียน (student)
        return view(
            'domains.tutor-profile.student.profile',
            compact('user')
        );
    }

    public function editProfile()
    {
        $tutorProfile = TutorProfile::firstOrNew(['Users_user_id' => Auth::id()]);
        $allSubjects = Subject::all();

        return view(
            'domains.tutor-profile.tutor.edit-profile',
            compact('tutorProfile', 'allSubjects')
        );
    }

    public function updateProfile(Request $request)
    {
        // 1. ตรวจสอบข้อมูลที่ส่งเข้ามา
        $request->validate([
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio'              => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'teaching_mode'    => ['nullable', 'in:online,onsite,both'],
            'line_id'          => ['nullable', 'string', 'max:255'],
            'discord_id'       => ['nullable', 'string', 'max:255'],
            'zoom_link'        => ['nullable', 'url', 'max:255'],
        ]);

        $userId = Auth::id();

        // 2. ดึงโปรไฟล์ติวเตอร์เดิม หรือสร้างขึ้นใหม่หากยังไม่มีในฐานข้อมูล
        $tutorProfile = TutorProfile::where('Users_user_id', $userId)->first();

        if (! $tutorProfile) {
            $tutorProfile = new TutorProfile();
            $tutorProfile->Users_user_id = $userId;
            $tutorProfile->tutor_id = 'TUT-' . strtoupper(Str::random(6));
        }

        // กำหนดค่าข้อมูลการสอนลง TutorProfile
        if ($request->has('bio')) {
            $tutorProfile->bio = $request->bio;
        }
        if ($request->has('experience_years')) {
            $tutorProfile->experience_years = $request->experience_years ?? 0;
        }
        if ($request->has('teaching_mode')) {
            $tutorProfile->teaching_mode = $request->teaching_mode ?? 'online';
        }

        // บันทึก TutorProfile
        $tutorProfile->save();

        // 3. ดึงข้อมูลช่องทางติดต่อ (Contact) หรือสร้างใหม่ตาม ER Diagram
        $contact = Contact::where('Users_user_id', $userId)->first();

        if (! $contact) {
            $contact = new Contact();
            $contact->contact_id = 'CON-' . strtoupper(Str::random(6));
            $contact->Users_user_id = $userId;
        }

        // กำหนดค่าลงตาราง contacts
        $contact->line_id = $request->line_id;
        $contact->discord_id = $request->discord_id;
        $contact->zoom_link = $request->zoom_link;

        // บันทึก Contact
        $contact->save();

        // 4. จัดการอัปโหลดรูปโปรไฟล์ (Users)
        if ($request->hasFile('avatar')) {
            $user = Auth::user();

            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->forceFill(['profile_picture' => $path])->save();
        }

        // 5. Redirect กลับไปยังหน้าโปรไฟล์ติวเตอร์ พร้อมแจ้งข้อความสำเร็จ
        return redirect()
            ->route('tutor.profile')
            ->with('success', 'บันทึกอัปเดตข้อมูลโปรไฟล์ติวเตอร์เรียบร้อยแล้ว');
    }

    /**
     * ส่งใบสมัครเป็นติวเตอร์ (draft/rejected -> pending)
     */
    public function submitApplication()
    {
        $profile = TutorProfile::where('Users_user_id', Auth::id())->first();

        if (! $profile) {
            return redirect()
                ->route('tutor.profile.edit')
                ->with('error', 'กรุณากรอกข้อมูลโปรไฟล์ติวเตอร์ก่อน');
        }

        if (in_array($profile->approval_status, ['pending', 'approved'], true)) {
            return back()->with('info', 'ใบสมัครนี้ส่งแล้วหรือได้รับอนุมัติแล้ว');
        }

        $missing = [];
        if (blank($profile->bio)) {
            $missing[] = 'แนะนำตัว';
        }
        if (blank($profile->teaching_mode)) {
            $missing[] = 'รูปแบบการสอน';
        }
        if (! $profile->subjects()->exists()) {
            $missing[] = 'วิชาที่สอนอย่างน้อย 1 วิชา';
        }

        if ($missing) {
            return back()->with('error', 'ข้อมูลยังไม่ครบ: ' . implode(', ', $missing));
        }

        $profile->update([
            'approval_status' => 'pending',
            'submitted_at'    => now(),
            'reject_reason'   => null,
        ]);

        return redirect()
            ->route('tutor.profile.edit')
            ->with('success', 'ส่งใบสมัครเรียบร้อยแล้ว กรุณารอการตรวจสอบ');
    }

    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tutors = TutorProfile::with(['user', 'subjects'])
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
        $tutors = TutorProfile::with(['user', 'subjects'])
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

        // ตรวจสอบอนุมัติ: อนุญาตให้ดูได้เลยหากมีข้อมูล หรือเป็นเจ้าของโปรไฟล์
        $isApproved = ($tutorProfile->approval_status === 'approved' || ($tutorProfile->status ?? null) === 'approved' || empty($tutorProfile->approval_status));

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

        return view(
            'domains.tutor-profile.tutor.show',
            compact('tutorProfile', 'isFavorite', 'allSubjects')
        );
    }
}