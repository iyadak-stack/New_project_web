<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\Booking\Models\Subject;
use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\Reportreview\Models\ReportReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Domains\TutorProfile\Services\TutorApplicationNotification;
use Illuminate\Validation\ValidationException;

class TutorProfileController extends Controller
{
    /**
     * แสดงรายละเอียดโปรไฟล์ติวเตอร์รายบุคคล (Public View / Route: /tutors/{id})
     */
    public function show($id)
    {
        $tutorProfile = TutorProfile::with([
            'subjects', 
            'user',
            'reviews.appointment.studentProfile.user'
        ])
        ->where('tutor_id', $id)
        ->firstOrFail();

        $isApproved = $tutorProfile->approval_status === 'approved';

        abort_unless($isApproved || $tutorProfile->Users_user_id == Auth::id() || Auth::user()?->role === 'admin' || Auth::user()?->current_role === 'admin', 404);

        $isFavorite = Auth::check() && Favorite::where('Users_user_id', Auth::id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->exists();

        $subject = $tutorProfile->subjects->first();

        $contact = Contact::where('Users_user_id', $tutorProfile->Users_user_id)->first();

        // แปลงวันเวลาในตารางเดิมให้ช่องเลือกวัน/เวลาในฟอร์มจองอ่านได้
        $slots = Availability::where('user_id', $tutorProfile->Users_user_id)
            ->where('start_datetime', '>', now('Asia/Bangkok')->format('Y-m-d H:i:s'))
            ->orderBy('start_datetime')->get();
        $availabilities = [];
        $confirmedLessons = $tutorProfile->appointments()->where('status', 'confirmed')->get();
        foreach ($slots as $slot) {
            // ไม่ให้เลือกช่วงที่มีนัดหมายยืนยันแล้ว ส่วนคำขอ pending รอให้ติวเตอร์ตัดสิน
            $isBusy = false;
            foreach ($confirmedLessons as $lesson) {
                if ($slot->start_datetime->format('Y-m-d H:i:s') < $lesson->end_datetime
                    && $slot->end_datetime->format('Y-m-d H:i:s') > $lesson->start_datetime) {
                    $isBusy = true;
                    break;
                }
            }
            if ($isBusy) {
                continue;
            }
            $availabilities[] = [
                'available_date' => $slot->start_datetime->format('Y-m-d'),
                'start_time' => $slot->start_datetime->format('H:i'),
                'end_time' => $slot->end_datetime->format('H:i'),
            ];
        }

        // หัวข้อรายงานใช้ข้อมูลจากฐานข้อมูล แทนการเขียน option ตายตัวใน View
        $reportReasons = ReportReason::orderBy('Reason_id')->get();

        return view('domains.tutor-profile.tutor.show', compact(
            'tutorProfile', 
            'subject', 
            'contact', 
            'isFavorite', 
            'availabilities',
            'reportReasons'
        ));
    }

    /**
     * 🟢 แสดงหน้าโปรไฟล์ตนเองของติวเตอร์ (Route: /tutor/profile)
     */
    public function profile()
    {
        $user = Auth::user();

        $tutorProfile = TutorProfile::with('subjects')
            ->where('Users_user_id', $user->user_id)
            ->first();

        $contact = Contact::where('Users_user_id', $user->user_id)
            ->first();

        return view('domains.tutor-profile.tutor.profile', compact('tutorProfile', 'user', 'contact'));
    }

    /**
     * แสดงฟอร์มแก้ไขโปรไฟล์ติวเตอร์ (Route: /tutor/profile/edit)
     */
    public function editProfile()
    {
        $user = Auth::user();

        $tutorProfile = TutorProfile::with('subjects')->firstOrNew(['Users_user_id' => $user->user_id]);
        $allSubjects = Subject::all();
        $contact = Contact::where('Users_user_id', $user->user_id)->first();

        return view('domains.tutor-profile.tutor.edit-profile', compact('tutorProfile', 'allSubjects', 'user', 'contact'));
    }

    /**
     * อัปเดตข้อมูลโปรไฟล์ติวเตอร์
     */
    public function updateProfile(Request $request)
    {
        $isApplication = $request->routeIs('tutor.application.submit');
        if ($isApplication) {
            abort_if($request->user()->role === 'admin' || $request->user()->current_role === 'admin', 403);
            $profile = $request->user()->tutorProfile()->first();
            if ($profile && in_array($profile->approval_status, ['pending', 'approved'])) {
                return back()->with('error', 'คำขอนี้ส่งแล้วหรือได้รับอนุมัติแล้ว');
            }
        }

        $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio'              => [$isApplication ? 'required' : 'nullable', 'string', 'max:3000'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'teaching_mode'    => [$isApplication ? 'required' : 'nullable', 'in:online,onsite,both'],
            'line_id'          => ['nullable', 'string', 'max:45'],
            'discord_id'       => ['nullable', 'string', 'max:255'],
            'zoom_link'        => ['nullable', 'url', 'max:255'],
            'google_meet_link' => ['nullable', 'url', 'max:255'],
            'subjects'         => [$isApplication ? 'required' : 'nullable', 'array', 'min:1'],
            'subjects.*'       => ['string', 'distinct', 'exists:subjects,subject_id'],
        ]);

        // บันทึกโปรไฟล์และคำขอพร้อมกัน หากผิดพลาดให้ยกเลิกชุดนี้
        DB::transaction(function () use ($request, $isApplication) {
            $user = Auth::user();
            $userId = $user->user_id;

            if ($isApplication) {
                // ตรวจซ้ำในชุดบันทึก เพื่อกันการกดส่งพร้อมกันสองครั้ง
                User::where('user_id', $userId)->lockForUpdate()->firstOrFail();
                $existing = TutorProfile::where('Users_user_id', $userId)->first();
                if ($existing && in_array($existing->approval_status, ['pending', 'approved'])) {
                    throw ValidationException::withMessages(['application' => 'คำขอนี้ส่งแล้วหรือได้รับอนุมัติแล้ว']);
                }
            }

            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');

            if ($request->hasFile('avatar')) {
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $user->profile_picture = $request->file('avatar')->store('avatars', 'public');
            }
            $user->save();

            $tutorProfile = TutorProfile::where('Users_user_id', $userId)->first();

            if (!$tutorProfile) {
                $tutorProfile = new TutorProfile();
                $tutorProfile->Users_user_id = $userId;
                $tutorProfile->tutor_id = 'TUT-' . strtoupper(Str::random(6));
            }

            $tutorProfile->bio = $request->input('bio');
            $tutorProfile->experience_years = $request->input('experience_years') ?? 0;
            $tutorProfile->teaching_mode = $request->input('teaching_mode', 'online');
            $tutorProfile->save();

            if ($request->has('subjects')) {
                $tutorProfile->subjects()->sync($request->input('subjects'));
            } else {
                $tutorProfile->subjects()->detach();
            }

            $contact = Contact::where('Users_user_id', $userId)->first();

            if (!$contact) {
                $contact = new Contact();
                $contact->contact_id = 'CON-' . strtoupper(Str::random(6));
                $contact->Users_user_id = $userId;
            }

            $contact->line_id = $request->input('line_id');
            $contact->discord_id = $request->input('discord_id');
            $contact->zoom_link = $request->input('zoom_link');
            $contact->google_meet_link = $request->input('google_meet_link');
            $contact->save();

            if ($isApplication) {
                $tutorProfile->update(['approval_status' => 'pending', 'submitted_at' => now(), 'reject_reason' => null]);
                TutorApplicationNotification::notifyAdmins();
                TutorApplicationNotification::send($userId, 'ส่งคำขอเป็นติวเตอร์แล้ว กรุณารอแอดมินอนุมัติ');
            }
        });

        if ($isApplication) {
            return redirect()->route('tutor.application.index')->with('success', 'ส่งคำขอแล้ว กรุณารอแอดมินอนุมัติ');
        }
        return redirect()->route('tutor.profile')->with('success', 'บันทึกอัปเดตข้อมูลโปรไฟล์ติวเตอร์เรียบร้อยแล้ว');
    }

    /**
     * ยื่นส่งใบสมัครติวเตอร์
     */
    public function submitApplication(Request $request)
    {
        // ใช้การตรวจและบันทึกข้อมูลเดียวกับฟอร์มแก้โปรไฟล์
        return $this->updateProfile($request);
    }
}
