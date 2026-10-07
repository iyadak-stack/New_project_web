<?php

namespace App\Domains\Scheduling\Http\Controllers;

use App\Domains\Booking\Models\Subject;
use App\Domains\Scheduling\Http\Requests\CheckScheduleRequest;
use App\Domains\Scheduling\Services\AvailabilityService;
use App\Domains\Scheduling\Services\LearningHistoryService;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Domains\Scheduling\Services\ScheduleBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckScheduleController extends Controller
{
    public function index(): View
    {
        return view('domains.scheduling.check-schedule.index');
    }

    public function history(LearningHistoryService $historyService): View
    {
        return view('domains.scheduling.check-schedule.history', [
            'appointments' => $historyService->forUser((string) auth()->id()),
        ]);
    }

    public function booking(
        CheckScheduleRequest $request,
        AvailabilityService $availabilityService
    ): View {
        $data = $request->validated();
        $studentId = (string) $request->user()->getAuthIdentifier();

        $student = StudentProfile::query()
            ->where('user_id', $studentId)
            ->first();

        $tutor = TutorProfile::query()
            ->where('Users_user_id', $data['tutor_id'])
            ->first();

        if (! $student || ! $tutor) {
            throw ValidationException::withMessages([
                'tutor_id' => 'ไม่พบโปรไฟล์นักเรียนหรือติวเตอร์ที่เลือก',
            ]);
        }

        $times = $availabilityService->findSharedTimes(
            $studentId,
            $data['tutor_id'],
            Carbon::parse($data['start_datetime']),
            Carbon::parse($data['end_datetime']),
        );

        if ($times !== [[
            'start' => Carbon::parse($data['start_datetime'])->format('Y-m-d H:i'),
            'end' => Carbon::parse($data['end_datetime'])->format('Y-m-d H:i'),
        ]]) {
            throw ValidationException::withMessages([
                'start_datetime' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาค้นหาใหม่',
            ]);
        }

        return view('domains.scheduling.check-schedule.booking', [
            'student' => $student,
            'tutor' => $tutor,
            'filters' => $data,
            'subjects' => Subject::query()
                ->orderBy('subject_name')
                ->get(),
        ]);
    }

    public function check(
        CheckScheduleRequest $request,
        AvailabilityService $availabilityService
    ): View {
        $data = $request->validated();
        $studentId = (string) $request->user()->getAuthIdentifier();

        $times = $availabilityService->findSharedTimes(
            $studentId,
            $data['tutor_id'],
            Carbon::parse($data['start_datetime']),
            Carbon::parse($data['end_datetime']),
        );

        return view('domains.scheduling.check-schedule.index', [
            'times' => $times,
            'filters' => $data,
        ]);
    }

    public function storeBooking(Request $request, ScheduleBookingService $bookingService): RedirectResponse
    {
        $data = $request->validate([
            'Student_profiles_student_id' => ['required', 'string', 'size:10'],
            'Tutor_profiles_tutor_id' => ['required', 'string', 'size:10'],
            'Subject_subject_id' => ['required', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
        ]);

        $data['Appointment_id'] = 'APP' . strtoupper(\Illuminate\Support\Str::random(7));
        $data['status'] = 'pending';

        $appointment = $bookingService->save($data);

        return redirect()->route('appointments.show', $appointment->getKey())->with('success', 'จองเรียนสำเร็จ');
    }
}