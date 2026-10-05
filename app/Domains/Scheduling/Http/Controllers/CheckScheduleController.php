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

    public function booking(CheckScheduleRequest $request, AvailabilityService $availabilityService): View
    {
        $data = $request->validated();
        $studentId = (string) $request->user()->getAuthIdentifier();
        $student = StudentProfile::query()->where('user_id', $studentId)->first();
        $tutor = TutorProfile::query()->where('user_id', $data['tutor_id'])->first();

        if (! $student || ! $tutor) {
            throw ValidationException::withMessages(['tutor_id' => 'ไม่พบโปรไฟล์นักเรียนหรือติวเตอร์ที่เลือก']);
        }

        $times = $availabilityService->findSharedTimes(
            $studentId, $data['tutor_id'],
            Carbon::parse($data['start_datetime']), Carbon::parse($data['end_datetime']),
        );

        if ($times !== [[
            'start' => Carbon::parse($data['start_datetime'])->format('Y-m-d H:i'),
            'end' => Carbon::parse($data['end_datetime'])->format('Y-m-d H:i'),
        ]]) {
            throw ValidationException::withMessages(['start_datetime' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาค้นหาใหม่']);
        }

        return view('domains.scheduling.check-schedule.booking', [
            'student' => $student, 'tutor' => $tutor, 'filters' => $data,
            'subjects' => Subject::query()->orderBy('subject_name')->get(),
        ]);
    }

    public function check(CheckScheduleRequest $request, AvailabilityService $availabilityService): View
    {
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
}
