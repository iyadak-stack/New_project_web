<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Auth\Models\Contact;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $studentProfile = StudentProfile::with('subjects')->where('user_id', $user->user_id)->first();
        $contact = Contact::where('Users_user_id', $user->user_id)->first();

        return view('domains.tutor-profile.student.profile', compact('studentProfile', 'user', 'contact'));
    }

    public function editProfile()
    {
        $user = Auth::user();

        // แก้คีย์ตรงนี้ให้ตรงกับ Database จริงคือ user_id
        $studentProfile = StudentProfile::with('subjects')->firstOrCreate(
            ['user_id' => $user->user_id],
            ['id' => strtoupper(Str::random(10))]
        );

        $allSubjects = Subject::all();

        return view('domains.tutor-profile.student.edit-profile', compact('studentProfile', 'allSubjects', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'bio' => ['nullable', 'string'],
            'line_id' => ['nullable', 'string', 'max:45'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['string', 'distinct', 'exists:subjects,subject_id'],
        ], [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'avatar.image' => 'ไฟล์ที่เลือกต้องเป็นรูปภาพ',
            'avatar.max' => 'รูปโปรไฟล์ต้องมีขนาดไม่เกิน 2 MB',
            'avatar.uploaded' => 'อัปโหลดรูปไม่สำเร็จ ไฟล์อาจใหญ่เกินไป',
        ]);

        $user = Auth::user();
        $userId = $user->user_id;

        // 1. บันทึกข้อมูลลงตาราง Users
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if ($request->hasFile('avatar')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('avatar')->store('profile_pictures', 'public');
        }

        $user->save();

        // 2. บันทึกข้อมูลลงตาราง Student_profiles (ใช้ user_id)
        $studentProfile = StudentProfile::firstOrCreate(
            ['user_id' => $userId],
            ['id' => strtoupper(Str::random(10))]
        );

        $studentProfile->bio = $request->bio;
        $studentProfile->save();

        // 3. บันทึกวิชาที่สนใจลงตาราง Student_profiles_has_Subject
        if ($request->has('subjects')) {
            $studentProfile->subjects()->sync($request->input('subjects'));
        } else {
            $studentProfile->subjects()->detach();
        }

        // 4. บันทึกข้อมูลลงตาราง Contact
        $contact = Contact::where('Users_user_id', $userId)->first();

        if (! $contact) {
            $contact = new Contact;
            $contact->contact_id = 'CON-'.strtoupper(Str::random(6));
            $contact->Users_user_id = $userId;
        }

        $contact->line_id = $request->line_id;
        $contact->discord_id = $request->discord_id;
        $contact->save();

        return redirect()
            ->route('home')
            ->with('success', 'อัปเดตโปรไฟล์นักเรียนเรียบร้อยแล้ว');
    }
}
