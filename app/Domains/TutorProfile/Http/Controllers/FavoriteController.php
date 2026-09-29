<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function storeTutor(TutorProfile $tutorProfile): RedirectResponse
    {
        Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'favoritable_type' => TutorProfile::class,
            'favoritable_id' => $tutorProfile->id,
        ]);

        return back()->with('success', 'Tutor added to favorites.');
    }

    public function destroyTutor(TutorProfile $tutorProfile): RedirectResponse
    {
        Favorite::where('user_id', auth()->id())
            ->where('favoritable_type', TutorProfile::class)
            ->where('favoritable_id', $tutorProfile->id)
            ->delete();

        return back()->with('success', 'Tutor removed from favorites.');
    }

    public function tutorFavorites()
    {
        $favorites = Favorite::where('user_id', auth()->id())
            ->where('favoritable_type', TutorProfile::class)
            ->with('favoritable.user')
            ->get();

        return view('domains.tutor-profile.tutor.favorites', compact('favorites'));
    }
}