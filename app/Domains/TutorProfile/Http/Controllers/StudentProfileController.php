<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function profile()
    {
        $studentProfile = StudentProfile::where('user_id', Auth::id())->first();
        return view('domains.tutor-profile.student.profile', compact('studentProfile'));
    }

    public function editProfile()
    {
        $studentProfile = StudentProfile::where('user_id', Auth::id())->first();
        return view('domains.tutor-profile.student.edit-profile', compact('studentProfile'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'], // เป็นรูปภาพ ขนาดไม่เกิน 2 MB
        ], [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'avatar.image' => 'ไฟล์ที่เลือกต้องเป็นรูปภาพ',
            'avatar.max' => 'รูปโปรไฟล์ต้องมีขนาดไม่เกิน 2 MB',
            'avatar.uploaded' => 'อัปโหลดรูปไม่สำเร็จ ไฟล์อาจใหญ่เกินไป',
        ]);

        // ---------- ส่วนที่เพิ่ม: บันทึกชื่อและรูปโปรไฟล์ลงตาราง Users ----------
        $user = Auth::user();

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if ($request->hasFile('avatar')) {
            // ลบรูปเก่าทิ้ง (ถ้ามี) แล้วเก็บรูปใหม่ไว้ที่ storage/app/public/profile_pictures
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('avatar')->store('profile_pictures', 'public');
        }

        $user->save();
        // ----------------------------------------------------------------------

        StudentProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'bio' => $request->bio,
            ]
        );

        // เปลี่ยนจาก 'student.profile' เป็นชื่อ Route หน้าหลักของคุณ (เช่น 'home' หรือ 'dashboard')
        return redirect()
            ->route('home')
            ->with('success', 'Student profile updated successfully.');
    }
}