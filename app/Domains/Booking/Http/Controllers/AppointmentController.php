<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use App\Domains\Booking\Models\Subject;
use App\Domains\Reportreview\Models\Review;
use App\Domains\Scheduling\Services\AvailabilityService;
use App\Domains\Scheduling\Services\ScheduleBookingService;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    // แสดงรายการนัดหมายทั้งหมด
    public function index()
    {
        $userId = (string) Auth::id();
        $studentProfileIds = StudentProfile::query()
            ->where('user_id', $userId)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->push($userId);
        $tutorProfileIds = TutorProfile::query()
            ->where('Users_user_id', $userId)
            ->pluck('tutor_id');

        $appointments = Appointment::query()
            ->with(['subject', 'tutorProfile.user.contact', 'studentProfile.user', 'studentUser', 'review'])
            ->where(function ($query) use ($studentProfileIds, $tutorProfileIds): void {
                $query->whereIn('Student_profiles_student_id', $studentProfileIds);

                if ($tutorProfileIds->isNotEmpty()) {
                    $query->orWhereIn('Tutor_profiles_tutor_id', $tutorProfileIds);
                }
            })
            ->orderByDesc('start_datetime')
            ->get();

        foreach ($appointments as $appointment) {
            $appointment->loadStudentProfile();
        }

        return view('domains.booking.appointments.index', compact('appointments', 'userId'));
    }

    // หน้าฟอร์มสร้างการนัดหมาย
    public function create()
    {
        $subjects = Subject::all();

        return view('domains.booking.appointments.create', compact('subjects'));
    }

    // บันทึกการนัดหมายใหม่
    public function store(Request $request)
    {
        abort_unless(Auth::user()->current_role === 'student', 403);
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'tutor_id' => ['required', 'exists:tutor_profiles,tutor_id'],
            'availability_id' => ['nullable', 'string', 'exists:availabilities,availability_id'],
            'start_datetime' => ['required_without:availability_id', 'nullable', 'date', 'after:now'],
            'end_datetime' => ['required_with:start_datetime', 'nullable', 'date', 'after:start_datetime'],
        ]);

        // ล็อกข้อมูลติวเตอร์ระหว่างจอง เพื่อไม่ให้สองคำขอจองเวลาซ้ำกัน
        // ลองบันทึกใหม่ได้ 3 ครั้ง หากคำขอพร้อมกันจับล็อกสวนกัน
        return DB::transaction(function () use ($data) {
            $tutor = TutorProfile::query()->where('tutor_id', $data['tutor_id'])->lockForUpdate()->firstOrFail();
            abort_unless($tutor->approval_status === 'approved' && $tutor->user?->is_active, 422, 'ติวเตอร์ยังไม่พร้อมรับจอง');
            abort_if($tutor->Users_user_id === Auth::id(), 422, 'ไม่สามารถจองตัวเองได้');
            abort_unless($tutor->subjects()->where('subjects.subject_id', $data['subject_id'])->exists(), 422);
            $contact = $tutor->user?->contact;
            // เรียก Service เดิมของเพื่อนเพื่อคำนวณเวลา ไม่เขียนการคำนวณซ้ำ
            $times = app(AvailabilityService::class)->resolveBookingTimes((string) $tutor->Users_user_id, $data);
            $start = $times['start'];
            $end = $times['end'];

            do {
                $appointmentId = 'APP'.Str::upper(Str::random(7));
            } while (Appointment::query()->whereKey($appointmentId)->exists());

            $appointment = app(ScheduleBookingService::class)->save([
                'Appointment_id' => $appointmentId,
                'status' => 'pending',
                'meeting_zoom' => $contact?->zoom_link ?? '',
                'meeting_meet' => $contact?->google_meet_link ?? '',
                'meeting_location' => $contact?->meeting_location ?? '',
                'start_datetime' => $start,
                'end_datetime' => $end,
                'Subject_subject_id' => $data['subject_id'],
                'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                'Student_profiles_student_id' => Auth::id(),
            ]);

            $notificationType = NotificationType::firstOrCreate(
                ['notification_type_id' => 'TYPE01'],
                ['type_name' => 'คำขอจองเรียน']
            );

            // แจ้งเตือนไปยังติวเตอร์
            Notification::create([
                'notification_id' => $this->newNotificationId(),
                'message' => 'มีการจองนัดหมายใหม่รอยืนยัน',
                'is_read' => 0,
                'Users_user_id' => $tutor->Users_user_id,
                'NotificationType_notification_type_id' => $notificationType->notification_type_id,
            ]);

            return redirect()
                ->route('tutor.show', $tutor->tutor_id)
                ->with('success', 'ส่งคำขอจองสำเร็จแล้ว');
        }, 3);
    }

    // ดูรายละเอียดนัดหมาย
    public function show($id)
    {
        $appointment = Appointment::query()
            ->with(['subject', 'tutorProfile.user.contact', 'studentProfile.user', 'studentUser', 'review'])
            ->findOrFail($id);

        $appointment->loadStudentProfile();

        $studentProfileIds = StudentProfile::query()
            ->where('user_id', (string) Auth::id())
            ->pluck('id')
            ->map(fn ($profileId): string => (string) $profileId);
        $tutorProfileIds = TutorProfile::query()
            ->where('Users_user_id', (string) Auth::id())
            ->pluck('tutor_id');

        abort_unless(
            (string) $appointment->Student_profiles_student_id === (string) Auth::id()
                || $studentProfileIds->contains((string) $appointment->Student_profiles_student_id)
                || $tutorProfileIds->contains((string) $appointment->Tutor_profiles_tutor_id),
            404
        );

        $isStudentBooking = $this->isStudentOwner($appointment);
        $isTutorBooking = $this->isTutorOwner($appointment);

        return view('domains.booking.appointments.show', compact('appointment', 'isStudentBooking', 'isTutorBooking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled'],
            'rejection_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:2000'],
        ]);
        // ตรวจ pending และบันทึกผลใน transaction เดียว กันรับ/ปฏิเสธสวนกัน
        $appointment = DB::transaction(function () use ($id, $data) {
            $appointment = Appointment::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($this->canManageAsTutor($appointment), 403);
            abort_unless($appointment->status === 'pending', 422);

            $appointment->update(['status' => $data['status'], 'rejection_reason' => $data['rejection_reason'] ?? null]);
            $studentUserId = $appointment->studentProfile?->user_id ?? $appointment->Student_profiles_student_id;
            $this->notify($studentUserId, 'สถานะการนัดหมาย '.$appointment->Appointment_id.' เปลี่ยนเป็น'.($data['status'] === 'confirmed' ? 'ยืนยันแล้ว' : 'ยกเลิกแล้ว'));

            return $appointment;
        });

        return redirect()->route('appointments.show', $appointment)->with('success', 'อัปเดตสถานะการนัดหมายแล้ว');
    }

    public function updateLesson(Request $request, Appointment $appointment)
    {
        abort_unless($this->canManageAsTutor($appointment), 403);
        abort_unless(in_array($appointment->status, ['pending', 'confirmed'], true), 422);
        // คืนค่าจากโปรไฟล์ หรือบันทึกข้อมูลที่แก้เฉพาะนัดหมายนี้
        if ($request->boolean('use_profile_defaults')) {
            $data = [
                'meeting_zoom' => null,
                'meeting_meet' => null,
                'meeting_location' => null,
                'lesson_details_customized' => false,
                'lesson_notes' => null,
            ];
        } else {
            $data = $request->validate([
                'meeting_zoom' => ['nullable', 'url:http,https', 'max:500'],
                'meeting_meet' => ['nullable', 'url:http,https', 'max:500'],
                'meeting_location' => ['nullable', 'string', 'max:500'],
                'lesson_notes' => ['nullable', 'string', 'max:3000'],
            ]);
            foreach (['meeting_zoom', 'meeting_meet', 'meeting_location'] as $field) {
                $data[$field] = $data[$field] ?? '';
            }
            $data['lesson_details_customized'] = true;
        }
        $appointment->update($data);
        $this->notify($appointment->studentProfile?->user_id ?? $appointment->Student_profiles_student_id, 'ติวเตอร์อัปเดตข้อมูลห้องเรียนของนัดหมาย '.$appointment->Appointment_id);

        return back()->with('success', 'บันทึกรายละเอียดการเรียนแล้ว');
    }

    public function confirm(Appointment $appointment)
    {
        return $this->updateStatus(new Request(['status' => 'confirmed']), $appointment->Appointment_id);
    }

    public function cancel(Appointment $appointment)
    {
        abort_unless($this->isStudentOwner($appointment) || ($this->canManageAsTutor($appointment)), 403);
        abort_unless(in_array($appointment->status, ['pending', 'confirmed'], true), 422);
        $appointment->update(['status' => 'cancelled']);
        $studentUserId = $appointment->studentProfile?->user_id ?? $appointment->Student_profiles_student_id;
        $this->notify($this->isTutorOwner($appointment) ? $studentUserId : $appointment->tutorProfile?->Users_user_id, 'การนัดหมาย '.$appointment->Appointment_id.' ถูกยกเลิก');

        return redirect()->route('appointments.index')->with('success', 'ยกเลิกการนัดหมายแล้ว');
    }

    // ตรวจสิทธิ์และสถานะซ้ำที่เซิร์ฟเวอร์ แม้ผู้ใช้จะส่งคำขอโดยไม่กดปุ่มในหน้าเว็บ
    public function complete(Appointment $appointment)
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = Appointment::whereKey($appointment->Appointment_id)->lockForUpdate()->firstOrFail();
            abort_unless($this->isStudentOwner($appointment), 403);
            abort_unless($appointment->canComplete(), 422, 'ต้องรอติวเตอร์ยืนยันและเรียนจบก่อนยืนยัน session');

            $appointment->update(['status' => 'completed']);
            $this->notify($appointment->tutorProfile?->Users_user_id, 'ผู้เรียนยืนยันเรียนจบแล้ว ('.$appointment->Appointment_id.')');

            return redirect()
                ->route('appointments.index')
                ->with('success', 'ยืนยันเรียนเสร็จแล้ว')
                ->with('review_appointment', $appointment->Appointment_id);
        });
    }

    // หนึ่งนัดหมายรีวิวได้ครั้งเดียว และต้องใช้ติวเตอร์ของนัดหมายนั้น
    public function storeReview(Request $request, Appointment $appointment)
    {
        return DB::transaction(function () use ($request, $appointment) {
            $appointment = Appointment::whereKey($appointment->Appointment_id)->lockForUpdate()->firstOrFail();
            abort_unless($this->isStudentOwner($appointment), 403);
            abort_unless($appointment->status === 'completed', 422);
            // รีวิวที่แอดมินซ่อนยังนับว่าเคยส่ง เพื่อกันส่งซ้ำหลังถูกซ่อน
            abort_if($appointment->review()->withTrashed()->exists(), 422, 'คุณส่งรีวิวสำหรับนัดหมายนี้แล้ว');

            $data = $request->validate([
                'rating' => ['required', 'integer', 'between:1,5'],
                'Comment' => ['required', 'string', 'max:2000'],
            ]);
            do {
                $reviewId = Str::upper(Str::random(10));
            } while (Review::query()->whereKey($reviewId)->exists());

            $appointment->review()->create([
                'Review_id' => $reviewId,
                'rating' => $data['rating'],
                'Comment' => $data['Comment'],
                'Tutor_profiles_tutor_id' => $appointment->Tutor_profiles_tutor_id,
            ]);
            $tutor = $appointment->tutorProfile;
            $tutor->update(['average_rating' => round($tutor->reviews()->avg('rating') ?? 0, 2)]);

            $this->notify($tutor->Users_user_id, 'มีรีวิวใหม่จากผู้เรียน คะแนน '.$data['rating'].'/5 สำหรับนัดหมาย '.$appointment->Appointment_id);

            return redirect()->route('appointments.index')->with('success', 'ขอบคุณสำหรับรีวิวติวเตอร์');
        });
    }

    private function isStudentOwner(Appointment $appointment): bool
    {
        // ข้อมูลเก่าบางรายการเก็บรหัสโปรไฟล์นักเรียน ส่วนข้อมูลใหม่เก็บรหัสผู้ใช้
        $userId = (string) Auth::user()->user_id;

        return (string) $appointment->Student_profiles_student_id === $userId
            || (string) $appointment->studentProfile?->user_id === $userId;
    }

    private function isTutorOwner(Appointment $appointment): bool
    {
        return (string) $appointment->tutorProfile?->Users_user_id === (string) Auth::user()->user_id;
    }

    // ผู้สอนต้องเป็นเจ้าของนัดหมาย ใช้บทบาทติวเตอร์ และผ่านการอนุมัติแล้ว
    private function canManageAsTutor(Appointment $appointment): bool
    {
        return $this->isTutorOwner($appointment)
            && Auth::user()->current_role === 'tutor'
            && $appointment->tutorProfile->approval_status === 'approved';
    }

    private function notify(?string $userId, string $message): void
    {
        if (! $userId) {
            return;
        }
        $type = NotificationType::firstOrCreate(['notification_type_id' => 'TYPE02'], ['type_name' => 'อัปเดตนัดหมาย']);
        Notification::create([
            'notification_id' => $this->newNotificationId(),
            'message' => $message,
            'is_read' => 0,
            'Users_user_id' => $userId,
            'NotificationType_notification_type_id' => $type->notification_type_id,
        ]);
    }

    private function newNotificationId(): string
    {
        do {
            $id = 'NOT'.Str::upper(Str::random(7));
        } while (Notification::query()->whereKey($id)->exists());

        return $id;
    }
}
