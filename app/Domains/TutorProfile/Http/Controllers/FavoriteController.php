<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class FavoriteController extends Controller
{
    public function storeTutor(TutorProfile $tutorProfile): RedirectResponse
    {
        abort_unless($tutorProfile->approval_status === 'approved', 404);
        $favorite = Favorite::withTrashed()
            ->where('Users_user_id', auth()->id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->first();

        // ถ้าเคยกดโปรดแล้วนำออก ให้คืนรายการเดิมแทนการสร้างซ้ำ
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

    public function destroyTutor(TutorProfile $tutorProfile): RedirectResponse
    {
        Favorite::where('Users_user_id', auth()->id())
            ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
            ->delete();

        return back()->with('success', 'นำติวเตอร์ออกจากรายการโปรดเรียบร้อยแล้ว');
    }

    public function tutorFavorites()
    {
        $favorites = Favorite::where('Users_user_id', auth()->id())
            ->whereNotNull('Tutor_profiles_tutor_id')
            ->whereHas('tutorProfile', fn ($query) => $query->approved())
            ->with('tutorProfile.user')
            ->get();

        return view(
            'domains.tutor-profile.tutor.favorites',
            compact('favorites')
        );
    }
}
