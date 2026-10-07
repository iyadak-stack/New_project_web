<?php

namespace App\Domains\Scheduling\Services;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AvailabilityService
{
    public function __construct(private ScheduleMatcher $scheduleMatcher) {}

    public function findSharedTimes(string $studentId, string $tutorId, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $studentTimes = $this->findAvailabilities($studentId, $rangeStart, $rangeEnd);
        $tutorTimes = $this->findAvailabilities($tutorId, $rangeStart, $rangeEnd);
        $bookedTimes = $this->findAppointments($studentId, $tutorId, $rangeStart, $rangeEnd);

        return $this->scheduleMatcher->match($studentTimes, $tutorTimes, $bookedTimes, $rangeStart, $rangeEnd);
    }

    private function findAvailabilities(string $userId, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        return Availability::query()
            ->where('user_id', $userId)
            ->where('start_datetime', '<', $rangeEnd)
            ->where('end_datetime', '>', $rangeStart)
            ->orderBy('start_datetime')
            ->get()
            ->all();
    }

    private function findAppointments(string $studentId, string $tutorId, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        if (! Schema::hasTable('appointments')
            || ! Schema::hasTable('student_profiles')
            || ! Schema::hasTable('tutor_profiles')
            || ! Schema::hasColumn('appointments', 'Student_profiles_student_id')
            || ! Schema::hasColumn('appointments', 'Tutor_profiles_tutor_id')
            || ! Schema::hasColumn('appointments', 'start_datetime')
            || ! Schema::hasColumn('appointments', 'end_datetime')) {
            return [];
        }

        $studentProfileIds = StudentProfile::query()
            ->whereIN('user_id',[ $studentId, $tutorId])
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        $tutorProfileIds = TutorProfile::query()
            ->whereIn('Users_user_id', [$studentId, $tutorId])
            ->pluck('tutor_id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        if ($studentProfileIds === [] && $tutorProfileIds === []) {
            return [];
        }

        $query = Appointment::query()
            ->where(function (Builder $query) use ($studentProfileIds, $tutorProfileIds): void {
                if ($studentProfileIds !== []) {
                    $query->whereIn('Student_profiles_student_id', $studentProfileIds);
                }

                if ($tutorProfileIds !== []) {
                    if ($studentProfileIds !== []) {
                        $query->orWhereIn('Tutor_profiles_tutor_id', $tutorProfileIds);
                    } else {
                        $query->whereIn('Tutor_profiles_tutor_id', $tutorProfileIds);
                    }
                }
            })
            ->whereNotNull('start_datetime')
            ->whereNotNull('end_datetime')
            ->where('start_datetime', '<', $rangeEnd)
            ->where('end_datetime', '>', $rangeStart);

        if (Schema::hasColumn('appointments', 'status')) {
            $query->where('status', '!=', 'cancelled');
        }

        return $query->orderBy('start_datetime')->get()->all();
    }
}