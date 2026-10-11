<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorSearchController extends Controller
{
    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tutors = TutorProfile::with(['user', 'subjects'])
            ->withCount('reviews')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subjects', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('subject_id', 'like', "%{$search}%")
                            ->orWhere('subject_name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        $subjects = Subject::with('tutors.user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('subject_id', 'like', "%{$search}%")
                    ->orWhere('subject_name', 'like', "%{$search}%");
            })
            ->get();

        return view('domains.tutor-profile.tutor.search', compact('tutors', 'subjects', 'search'));
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
        if ($tutor instanceof TutorProfile) {
            $tutorProfile = $tutor;
        } else {
            $tutorProfile = TutorProfile::where('tutor_id', $tutor)
                ->orWhere('Users_user_id', $tutor)
                ->firstOrFail();
        }

        $isApproved = ($tutorProfile->approval_status === 'approved' || ($tutorProfile->status ?? null) === 'approved' || empty($tutorProfile->approval_status));

        abort_unless($isApproved || $tutorProfile->Users_user_id == Auth::id(), 404);

        $tutorProfile->load(['user', 'subjects', 'reviews', 'appointments']);

        $isFavorite = false;
        if (Auth::check()) {
            $isFavorite = Favorite::where('Users_user_id', Auth::id())
                ->where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)
                ->exists();
        }

        $allSubjects = Subject::all();

        return view('domains.tutor-profile.tutor.show', compact('tutorProfile', 'isFavorite', 'allSubjects'));
    }
}