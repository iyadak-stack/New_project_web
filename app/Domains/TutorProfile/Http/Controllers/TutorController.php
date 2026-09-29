<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function home()
    {
        $topTutors = TutorProfile::with(['user', 'subjects'])
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->take(5)
            ->get();

        $topSubjects = Subject::with('tutors')
            ->take(5)
            ->get();

        return view('welcome', compact(
            'topTutors',
            'topSubjects'
        ));
    }

    public function profile()
    {
        $tutorProfile = TutorProfile::where('user_id', auth()->id())->first();

        return view('domains.tutor-profile.tutor.profile', compact('tutorProfile'));
    }

    public function editProfile()
    {
        $tutorProfile = TutorProfile::where('user_id', auth()->id())->first();

        return view('domains.tutor-profile.tutor.edit-profile', compact('tutorProfile'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'bio' => ['nullable', 'string'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'teaching_mode' => ['required', 'in:online,onsite,both'],
        ]);

        $tutorProfile = TutorProfile::where('user_id', auth()->id())->firstOrFail();

        $tutorProfile->update([
            'bio' => $request->bio,
            'experience_years' => $request->experience_years,
            'teaching_mode' => $request->teaching_mode,
        ]);

        return redirect()
            ->route('tutor.profile')
            ->with('success', 'Tutor profile updated successfully.');
    }
    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tutors = TutorProfile::with(['user', 'subjects'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    // Search by Tutor name
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    // OR search by Subject name
                    ->orWhereHas('subjects', function ($subjectQuery) use ($search) {
                        $subjectQuery->where(
                            'subject_name',
                            'like',
                            "%{$search}%"
                        );
                    });
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        /*
        * Subjects shown in the result should be
        * the subjects taught by the tutors found above.
        */
        $subjects = $tutors
            ->flatMap(function ($tutor) {
                return $tutor->subjects;
            })
            ->unique('Subjec_id')
            ->values();

        /*
        * If the search matches a Subject directly,
        * also make sure that subject appears in the result.
        */
        if ($search !== '') {
            $matchedSubjects = Subject::with('tutors.user')
                ->where(
                    'subject_name',
                    'like',
                    "%{$search}%"
                )
                ->get();

            $subjects = $subjects
                ->merge($matchedSubjects)
                ->unique('Subjec_id')
                ->values();
        }

        return view('domains.tutor-profile.tutor.search', compact(
            'tutors',
            'subjects',
            'search'
        ));
    }
    public function ranking()
    {
        $tutors = TutorProfile::with('user')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        return view('domains.tutor-profile.tutor.ranking', compact('tutors'));
    }

    public function show(TutorProfile $tutorProfile)
    {
        $tutorProfile->load(['user', 'subjects']);

        $isFavorite = Favorite::where('user_id', auth()->id())
            ->where('favoritable_type', TutorProfile::class)
            ->where('favoritable_id', $tutorProfile->id)
            ->exists();

        return view('domains.tutor-profile.tutor.show', compact(
            'tutorProfile',
            'isFavorite'
        ));
    }
}