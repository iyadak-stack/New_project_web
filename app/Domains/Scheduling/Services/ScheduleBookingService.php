<?php

namespace App\Domains\Scheduling\Services;

use App\Domains\Booking\Models\Appointment;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ScheduleBookingService
{
    public function save(array $data, ?Appointment $appointment = null): Appointment
    {
        Validator::make($data, [
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'status' => 'required|in:pending,confirmed,cancelled',
        ])->validate();

        return DB::transaction(function () use ($data, $appointment): Appointment {
            $student = StudentProfile::query()->whereKey($data['Student_profiles_student_id'])->first();
            $tutor = TutorProfile::query()->whereKey($data['Tutor_profiles_tutor_id'])->first();

            if (! $student || ! $tutor) {
                throw ValidationException::withMessages([
                    'student_id' => 'ไม่พบโปรไฟล์นักเรียนหรือติวเตอร์ที่เลือก',
                ]);
            }

            if ((string) $student->user_id === (string) $tutor->Users_user_id) {
                throw ValidationException::withMessages(['tutor_id' => 'ไม่สามารถจองเรียนกับตัวเองได้']);
            }

            $userIds = [$student->user_id, $tutor->Users_user_id];
            $studentIds = StudentProfile::query()->whereIn('user_id', $userIds)
                ->orderBy('id')->lockForUpdate()->toBase()->get()->pluck('id');
            $tutorIds = TutorProfile::query()->whereIn('Users_user_id', $userIds)
                ->orderBy('tutor_id')->lockForUpdate()->toBase()->get()->pluck('tutor_id');

            if (! $appointment) {
                $times = app(AvailabilityService::class)->findSharedTimes(
                    (string) $student->user_id, (string) $tutor->Users_user_id,
                    Carbon::parse($data['start_datetime']),
                    Carbon::parse($data['end_datetime']),
                );

                if ($times !== [[
                    'start' => Carbon::parse($data['start_datetime'])->format('Y-m-d H:i'),
                    'end' => Carbon::parse($data['end_datetime'])->format('Y-m-d H:i'),
                ]]) {
                    throw ValidationException::withMessages(['start_datetime' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาเลือกเวลาอื่น']);
                }
            }

            if ($data['status'] !== 'cancelled') {
                $conflict = Appointment::query()
                    ->where(function ($query) use ($studentIds, $tutorIds): void {
                        $query->whereIn('Student_profiles_student_id', $studentIds)
                            ->orWhereIn('Tutor_profiles_tutor_id', $tutorIds);
                    })
                    ->where('status', '!=', 'cancelled')
                    ->where('start_datetime', '<', $data['end_datetime'])
                    ->where('end_datetime', '>', $data['start_datetime'])
                    ->when($appointment, fn ($query) => $query->where('Appointment_id', '!=', $appointment->getKey()))
                    ->lockForUpdate()
                    ->first();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'start_datetime' => 'นักเรียนหรือติวเตอร์มีนัดในช่วงเวลานี้แล้ว กรุณาเลือกเวลาอื่น',
                    ]);
                }
            }

            if ($appointment) {
                $appointment->update($data);

                return $appointment;
            }

            return Appointment::create($data);
        }, 3);
    }
}
