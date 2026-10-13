<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class FavoriteController extends Controller
{
    public function storeTutor($id): RedirectResponse
    {
        // ค้นหา TutorProfile จากคีย์จริง (tutor_id)
        $tutorProfile = TutorProfile::where('tutor_id', $id)->firstOrFail();

        // ผู้ใช้ไม่ควรเพิ่มตัวเองเป็นผู้สอนที่ชื่นชอบ
        abort_if($tutorProfile->Users_user_id === auth()->id(), 422);

        $favorite = Favorite::withTrashed()
            ->where('Users_user_id', auth()->id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->first();

        if ($favorite) {
            $favorite->restore();
        } else {
            Favorite::create([
                'favorite_id' => strtoupper(Str::random(10)),
                'Users_user_id' => auth()->id(),
                'Tutor_profiles_tutor_id' => $tutorProfile->tutor_id,
                'Subject_subject_id' => null,
            ]);
        }

        return back()->with('success', 'เพิ่มติวเตอร์ในรายการโปรดเรียบร้อยแล้ว');
    }

    public function destroyTutor($id): RedirectResponse
    {
        // ค้นหา TutorProfile ก่อนลบ
        $tutorProfile = TutorProfile::where('tutor_id', $id)->firstOrFail();

        Favorite::where('Users_user_id', auth()->id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->delete();

        return back()->with('success', 'นำติวเตอร์ออกจากรายการโปรดเรียบร้อยแล้ว');
    }

    public function tutorFavorites()
    {
        // แสดงเฉพาะรายการของคนที่เข้าสู่ระบบ และข้ามโปรไฟล์ที่ไม่มีผู้ใช้แล้ว
        // with โหลดผู้ใช้และวิชามาพร้อมกัน เพื่อลดการเรียกฐานข้อมูลใน foreach
        $favorites = Favorite::where('Users_user_id', auth()->id())
            ->whereNotNull('Tutor_profiles_tutor_id')
            ->whereHas('tutorProfile.user')
            ->whereHas('tutorProfile', function ($query) {
                // ไม่แสดงลิงก์ของผู้ที่ยังไม่อนุมัติหรือบัญชีถูกระงับ
                $query->approved()->forStudentList();
            })
            ->with(['tutorProfile.user', 'tutorProfile.subjects'])
            ->orderByDesc('created_at')
            ->get();

        return view(
            'domains.tutor-profile.tutor.favorites',
            compact('favorites')
        );
    }
}
