<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\Review;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $isTutor = $user->current_role === 'tutor';
        $profile = $isTutor ? $user->tutorProfile : $user->studentProfile;
        $profileId = $profile?->id;
        $foreignKey = $isTutor ? 'Tutor_profiles_tutor_id' : 'Student_profiles_student_id';

        $appointments = Appointment::with(['subject', 'tutorProfile.user', 'studentProfile.user'])
            ->where($foreignKey, $profileId ?? '')
            ->orderByDesc('start_datetime')
            ->limit(5)
            ->get();

        $reviewQuery = Review::query();
        if ($isTutor) {
            $reviewQuery->where('Tutor_profiles_tutor_id', $profileId ?? '');
        } else {
            $reviewQuery->whereHas('appointment', fn ($query) => $query->where('Student_profiles_student_id', $profileId ?? ''));
        }

        return view('dashboard', [
            'isTutor' => $isTutor,
            'appointmentCount' => Appointment::where($foreignKey, $profileId ?? '')->count(),
            'reviewCount' => $reviewQuery->count(),
            'reportCount' => Report::where('Users_user_id', $user->user_id)->count(),
            'appointments' => $appointments,
        ]);
    }
}
