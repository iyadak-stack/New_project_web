<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\Booking\Models\Subject;
use App\Domains\Auth\Models\Contact;
use App\Domains\Scheduling\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'reviews.studentProfile.user'
        ])
        ->where('tutor_id', $id)
        ->firstOrFail();

        $isApproved = $tutorProfile->approval_status === 'approved'
            || empty($tutorProfile->approval_status);

        abort_unless($isApproved || $tutorProfile->Users_user_id == Auth::id(), 404);

        $isFavorite = Auth::check() && Favorite::where('Users_user_id', Auth::id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->exists();

        $subject = $tutorProfile->subjects->first();

        $contact = Contact::where('Users_user_id', $tutorProfile->Users_user_id)
            ->orWhere('user_id', $tutorProfile->Users_user_id)
            ->first();

        $availabilities = Availability::where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->where('is_available', true)
            ->get();

        return view('domains.tutor-profile.tutor.show', compact(
            'tutorProfile', 
            'subject', 
            'contact', 
            'isFavorite', 
            'availabilities'
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
            ->orWhere('user_id', $user->user_id)
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
        $contact = Contact::where('Users_user_id', $user->user_id)->orWhere('user_id', $user->user_id)->first();

        return view('domains.tutor-profile.tutor.edit-profile', compact('tutorProfile', 'allSubjects', 'user', 'contact'));
    }

    /**
     * อัปเดตข้อมูลโปรไฟล์ติวเตอร์
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio'              => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'teaching_mode'    => ['nullable', 'in:online,onsite,both'],
            'line_id'          => ['nullable', 'string', 'max:255'],
            'discord_id'       => ['nullable', 'string', 'max:255'],
            'zoom_link'        => ['nullable', 'url', 'max:255'],
            'google_meet_link' => ['nullable', 'url', 'max:255'],
            'subjects'         => ['nullable', 'array'],
            'subjects.*'       => ['string'],
        ]);

        $user = Auth::user();
        $userId = $user->user_id;

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
        $tutorProfile->experience_years = $request->input('experience_years', 0);
        $tutorProfile->teaching_mode = $request->input('teaching_mode', 'online');
        $tutorProfile->save();

        if ($request->has('subjects')) {
            $tutorProfile->subjects()->sync($request->input('subjects'));
        } else {
            $tutorProfile->subjects()->detach();
        }

        $contact = Contact::where('Users_user_id', $userId)->orWhere('user_id', $userId)->first();

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

        return redirect()->route('tutor.profile')->with('success', 'บันทึกอัปเดตข้อมูลโปรไฟล์ติวเตอร์เรียบร้อยแล้ว');
    }

    /**
     * ยื่นส่งใบสมัครติวเตอร์
     */
    public function submitApplication()
    {
        $profile = TutorProfile::where('Users_user_id', Auth::id())->first();

        if (!$profile) {
            return redirect()->route('tutor.profile.edit')->with('error', 'กรุณากรอกข้อมูลโปรไฟล์ติวเตอร์ก่อน');
        }

        if (in_array($profile->approval_status, ['pending', 'approved'], true)) {
            return back()->with('info', 'ใบสมัครนี้ส่งแล้วหรือได้รับอนุมัติแล้ว');
        }

        $missing = [];
        if (blank($profile->bio)) $missing[] = 'แนะนำตัว';
        if (blank($profile->teaching_mode)) $missing[] = 'รูปแบบการสอน';
        if (!$profile->subjects()->exists()) $missing[] = 'วิชาที่สอนอย่างน้อย 1 วิชา';

        if ($missing) {
            return back()->with('error', 'ข้อมูลยังไม่ครบ: ' . implode(', ', $missing));
        }

        $profile->update([
            'approval_status' => 'pending',
            'submitted_at'    => now(),
            'reject_reason'   => null,
        ]);

        return redirect()->route('tutor.profile.edit')->with('success', 'ส่งใบสมัครเรียบร้อยแล้ว กรุณารอการตรวจสอบ');
    }
}