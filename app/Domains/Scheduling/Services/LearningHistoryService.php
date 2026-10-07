<?php

namespace App\Domains\Scheduling\Services;

use App\Domains\Booking\Models\Appointment;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LearningHistoryService
{
    public function forUser(string $userId): Collection
    {
        $studentIds = StudentProfile::query()->where('user_id', $userId)->toBase()->pluck('id');
        $tutorIds = TutorProfile::query()->where('Users_user_id', $userId)->toBase()->pluck('tutor_id');

        return Appointment::query()
            ->with('subject')
            ->where(function ($query) use ($studentIds, $tutorIds): void {
                $query->whereIn('Student_profiles_student_id', $studentIds)
                    ->orWhereIn('Tutor_profiles_tutor_id', $tutorIds);
            })
            ->whereNotNull('end_datetime')
            ->where('end_datetime', '<=', Carbon::now())
            ->orderByDesc('end_datetime')
            ->get()
            ->map(function (Appointment $appointment) use ($studentIds, $tutorIds): array {
                $roles = [];

                if ($studentIds->contains($appointment->Student_profiles_student_id)) {
                    $roles[] = 'นักเรียน';
                }

                if ($tutorIds->contains($appointment->Tutor_profiles_tutor_id)) {
                    $roles[] = 'ติวเตอร์';
                }

                return [
                    'id' => $appointment->getKey(),
                    'subject' => $appointment->subject?->subject_name ?? $appointment->Subject_subject_id,
                    'role' => implode(' / ', $roles),
                    'start' => $appointment->start_datetime ? Carbon::parse($appointment->start_datetime)->format('d/m/Y H:i') : '-',
                    'end' => Carbon::parse($appointment->end_datetime)->format('d/m/Y H:i'),
                    'status' => $appointment->status,
                ];
            });
    }
}
