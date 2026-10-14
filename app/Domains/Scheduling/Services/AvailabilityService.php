<?php

namespace App\Domains\Scheduling\Services;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function __construct(private ScheduleMatcher $scheduleMatcher) {}

    public function findSharedTimes(string $studentId, string $tutorId, Carbon $rangeStart, Carbon $rangeEnd, ?string $exceptAppointmentId = null): array
    {
        $studentTimes = $this->findAvailabilities($studentId, $rangeStart, $rangeEnd);
        $tutorTimes = $this->findAvailabilities($tutorId, $rangeStart, $rangeEnd);
        $bookedTimes = $this->findAppointments($studentId, $tutorId, $rangeStart, $rangeEnd, $exceptAppointmentId);

        return $this->scheduleMatcher->match($studentTimes, $tutorTimes, $bookedTimes, $rangeStart, $rangeEnd);
    }

    public function findTutorAvailableTimes(string $tutorUserId, ?Carbon $rangeStart = null, ?Carbon $rangeEnd = null): array
    {
        $rangeStart = $rangeStart ? $rangeStart->copy() : Carbon::now();
        if (! $rangeEnd) {
            $lastEnd = Availability::query()->where('user_id', $tutorUserId)->max('end_datetime');
            if (! $lastEnd) {
                return [];
            }
            $rangeEnd = Carbon::parse($lastEnd);
        }
        if ($rangeEnd->lessThanOrEqualTo($rangeStart)) {
            return [];
        }

        $tutorTimes = $this->findAvailabilities($tutorUserId, $rangeStart, $rangeEnd);
        $bookedTimes = $this->findAppointments($tutorUserId, $tutorUserId, $rangeStart, $rangeEnd, null);

        // ใช้เวลาติวเตอร์ทั้งสองฝั่ง เพื่อรวมช่วงติดกันและตัดนัดด้วย logic เดียวกับ CheckSchedule
        return $this->scheduleMatcher->match($tutorTimes, $tutorTimes, $bookedTimes, $rangeStart, $rangeEnd);
    }

    public function resolveBookingTimes(string $tutorUserId, array $data): array
    {
        Validator::make($data, [
            'availability_id' => 'nullable|string',
            'start_datetime' => 'required_without:availability_id|nullable|date|after:now',
            'end_datetime' => 'required_with:start_datetime|nullable|date|after:start_datetime',
        ])->validate();

        $availability = null;
        if (! empty($data['availability_id'])) {
            $availability = Availability::query()
                ->whereKey($data['availability_id'])
                ->where('user_id', $tutorUserId)
                ->when(DB::transactionLevel() > 0, fn ($query) => $query->lockForUpdate())
                ->first();
            if (! $availability) {
                throw ValidationException::withMessages(['availability_id' => 'ไม่พบเวลาว่างของติวเตอร์ที่เลือก']);
            }
        }

        $start = Carbon::parse($data['start_datetime'] ?? $availability->start_datetime);
        $end = Carbon::parse($data['end_datetime'] ?? $availability->end_datetime);
        if ($start->lessThanOrEqualTo(Carbon::now())
            || $end->lessThanOrEqualTo($start)
            || ($availability && ($start->lessThan($availability->start_datetime)
                || $end->greaterThan($availability->end_datetime)))) {
            throw ValidationException::withMessages(['start_datetime' => 'ช่วงเวลาที่เลือกไม่อยู่ในเวลาว่างที่จองได้']);
        }

        $times = $this->findTutorAvailableTimes($tutorUserId, $start, $end);
        if ($times !== [[
            'start' => $start->format('Y-m-d H:i'),
            'end' => $end->format('Y-m-d H:i'),
        ]]) {
            throw ValidationException::withMessages(['start_datetime' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาเลือกเวลาอื่น']);
        }

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ];
    }

    private function findAvailabilities(string $userId, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        return Availability::query()
            ->where('user_id', $userId)
            ->where('start_datetime', '<', $rangeEnd)
            ->where('end_datetime', '>', $rangeStart)
            ->orderBy('start_datetime')
            ->when(DB::transactionLevel() > 0, fn ($query) => $query->lockForUpdate())
            ->get()
            ->all();
    }

    private function findAppointments(string $studentId, string $tutorId, Carbon $rangeStart, Carbon $rangeEnd, ?string $exceptAppointmentId): array
    {
        if (! Schema::hasTable('appointments')
            || ! Schema::hasTable('student_profiles')
            || ! Schema::hasTable('tutor_profiles')
            || ! Schema::hasColumn('appointments', 'Student_profiles_student_id')
            || ! Schema::hasColumn('appointments', 'Tutor_profiles_tutor_id')
            || ! Schema::hasColumn('appointments', 'start_datetime')
            || ! Schema::hasColumn('appointments', 'end_datetime')) {
            throw ValidationException::withMessages(['start_datetime' => 'ข้อมูลนัดหมายยังไม่พร้อม ยังตรวจเวลาว่างไม่ได้']);
        }

        $studentProfileIds = StudentProfile::query()
            ->whereIn('user_id', [$studentId, $tutorId])
            ->toBase()
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();
        $studentProfileIds = array_unique(array_merge($studentProfileIds, [$studentId, $tutorId]));
        $tutorProfileIds = TutorProfile::query()
            ->whereIn('Users_user_id', [$studentId, $tutorId])
            ->toBase()
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
            ->where('end_datetime', '>', $rangeStart)
            ->when($exceptAppointmentId, fn ($query) => $query->where('Appointment_id', '!=', $exceptAppointmentId));

        if (Schema::hasColumn('appointments', 'status')) {
            $query->where('status', '!=', 'cancelled');
        }

        return $query->orderBy('start_datetime')
            ->when(DB::transactionLevel() > 0, fn ($query) => $query->lockForUpdate())
            ->get()->all();
    }
}
