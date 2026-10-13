<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', 'in:all,pending,confirmed,cancelled']]);
        $user = $request->user();
        $status = $request->query('status', 'all');
        $query = Appointment::with(['subject', 'studentProfile.user', 'tutorProfile.user']);

        // นักเรียนใช้ student_profiles.id ติวเตอร์ใช้ tutor_profiles.tutor_id
        if ($user->current_role === 'tutor') {
            $query->where('Tutor_profiles_tutor_id', $user->tutorProfile?->tutor_id);
        } else {
            $query->where('Student_profiles_student_id', $user->studentProfile?->id);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        $appointments = $query->orderBy('start_datetime')->get();

        return view('domains.booking.appointments.index', compact('appointments', 'status'));
    }

    public function create(Request $request)
    {
        if (! $request->filled('tutor_id')) {
            return redirect()->route('tutor.search');
        }

        // จองจากโปรไฟล์ เพื่อเลือกเฉพาะวิชาและเวลาที่ติวเตอร์เปิดไว้
        return redirect()->route('tutor.show', $request->input('tutor_id'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->current_role === 'student', 403);
        $data = $request->validate([
            'subject_id' => ['required', 'string', 'exists:subjects,subject_id'],
            'tutor_id' => ['required', 'string', 'exists:tutor_profiles,tutor_id'],
            'appointment_date' => ['required_without:start_datetime', 'nullable', 'date_format:Y-m-d'],
            'appointment_time' => ['required_without:start_datetime', 'nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9] - ([01][0-9]|2[0-3]):[0-5][0-9]( น\.)?$/'],
            'start_datetime' => ['nullable', 'date'],
            'end_datetime' => ['required_with:start_datetime', 'nullable', 'date', 'after:start_datetime'],
        ]);
        if ($request->filled('start_datetime')) {
            $start = Carbon::parse($data['start_datetime'], 'Asia/Bangkok')->timezone('Asia/Bangkok');
            $end = Carbon::parse($data['end_datetime'], 'Asia/Bangkok')->timezone('Asia/Bangkok');
        } else {
            $times = explode(' - ', str_replace(' น.', '', $data['appointment_time']));
            $start = Carbon::parse($data['appointment_date'].' '.$times[0], 'Asia/Bangkok');
            $end = Carbon::parse($data['appointment_date'].' '.$times[1], 'Asia/Bangkok');
        }
        if (! $start->isFuture() || $end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['appointment_time' => 'กรุณาเลือกเวลาในอนาคต และเวลาสิ้นสุดต้องหลังเวลาเริ่ม']);
        }

        DB::transaction(function () use ($request, $data, $start, $end) {
            $tutor = TutorProfile::with('user')->lockForUpdate()->findOrFail($data['tutor_id']);
            if (! $tutor->user || ! $tutor->user->is_active || $tutor->Users_user_id === $request->user()->user_id) {
                throw ValidationException::withMessages(['tutor_id' => 'ไม่สามารถจองติวเตอร์คนนี้ได้']);
            }
            if (! $tutor->subjects()->where('subjects.subject_id', $data['subject_id'])->exists()) {
                throw ValidationException::withMessages(['subject_id' => 'ติวเตอร์ไม่ได้เปิดสอนวิชานี้']);
            }
            $student = StudentProfile::firstOrCreate(['user_id' => $request->user()->user_id], ['id' => strtoupper(Str::random(10))]);
            StudentProfile::where('id', $student->id)->lockForUpdate()->first();
            $startTime = $start->format('Y-m-d H:i:s');
            $endTime = $end->format('Y-m-d H:i:s');

            $available = Availability::where('user_id', $tutor->Users_user_id)
                ->where('start_datetime', '<=', $startTime)->where('end_datetime', '>=', $endTime)->exists();
            if (! $available) {
                throw ValidationException::withMessages(['appointment_time' => 'ช่วงเวลานี้ไม่อยู่ในเวลาว่างของติวเตอร์แล้ว']);
            }
            $studentBusy = Appointment::where('Student_profiles_student_id', $student->id)
                ->whereIn('status', ['pending', 'confirmed'])->where('start_datetime', '<', $endTime)->where('end_datetime', '>', $startTime)->exists();
            $tutorBusy = Appointment::where('Tutor_profiles_tutor_id', $tutor->tutor_id)
                ->where('status', 'confirmed')->where('start_datetime', '<', $endTime)->where('end_datetime', '>', $startTime)->exists();
            if ($studentBusy || $tutorBusy) {
                throw ValidationException::withMessages(['appointment_time' => 'มีนัดหมายที่ทับกับช่วงเวลานี้แล้ว']);
            }

            $appointment = Appointment::create([
                'Appointment_id' => strtoupper(Str::random(10)),
                'status' => 'pending', 'start_datetime' => $startTime, 'end_datetime' => $endTime,
                'Subject_subject_id' => $data['subject_id'],
                'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                // ใช้ผู้เรียนจากบัญชีที่ล็อกอิน ไม่รับ student_id ของคนอื่นจากฟอร์ม
                'Student_profiles_student_id' => $student->id,
            ]);
            $this->notify($tutor->Users_user_id, 'มีคำขอเรียนใหม่ รหัส '.$appointment->Appointment_id, 'BOOKREQ001', 'คำขอจองเรียน');
        });

        return redirect()->route('appointments.index')->with('success', 'ส่งคำขอเรียนแล้ว กรุณารอติวเตอร์ยืนยัน');
    }

    public function show(Request $request, string $id)
    {
        $appointment = Appointment::with(['subject', 'studentProfile.user', 'tutorProfile.user.contact'])->findOrFail($id);
        $userId = $request->user()->user_id;
        $isAdmin = $request->user()->role === 'admin' || $request->user()->current_role === 'admin';
        abort_unless($isAdmin || $appointment->studentProfile?->user_id === $userId || $appointment->tutorProfile?->Users_user_id === $userId, 403);

        return view('domains.booking.appointments.show', compact('appointment'));
    }

    public function accept(Request $request, string $id)
    {
        return $this->respond($request, $id, 'confirmed');
    }

    public function reject(Request $request, string $id)
    {
        return $this->respond($request, $id, 'cancelled');
    }

    public function confirm(Request $request, string $id)
    {
        return $this->accept($request, $id);
    }

    public function updateStatus(Request $request, string $id)
    {
        // Route เดิมต้องใช้การตรวจเจ้าของและสถานะเหมือนปุ่มยืนยัน/ปฏิเสธ
        $data = $request->validate(['status' => ['required', 'in:confirmed,cancelled']]);

        return $this->respond($request, $id, $data['status']);
    }

    private function respond(Request $request, string $id, string $status)
    {
        $appointment = Appointment::findOrFail($id);
        $tutor = $request->user()->tutorProfile;
        abort_unless($request->user()->current_role === 'tutor' && $tutor && $appointment->Tutor_profiles_tutor_id === $tutor->tutor_id, 403);

        // สถานะและแจ้งเตือนต้องบันทึกสำเร็จด้วยกัน
        DB::transaction(function () use ($id, $tutor, $status) {
            TutorProfile::where('tutor_id', $tutor->tutor_id)->lockForUpdate()->firstOrFail();
            $appointment = Appointment::with('studentProfile')->lockForUpdate()->findOrFail($id);
            if ($appointment->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'คำขอนี้ถูกดำเนินการแล้ว']);
            }
            if (! $appointment->studentProfile) {
                throw ValidationException::withMessages(['status' => 'ไม่พบข้อมูลผู้เรียนของคำขอนี้']);
            }
            StudentProfile::where('id', $appointment->Student_profiles_student_id)->lockForUpdate()->first();
            if ($status === 'confirmed') {
                $conflict = Appointment::where('status', 'confirmed')
                    ->where('Appointment_id', '!=', $id)
                    ->where('start_datetime', '<', $appointment->end_datetime)
                    ->where('end_datetime', '>', $appointment->start_datetime)
                    ->where(function ($query) use ($appointment) {
                        $query->where('Tutor_profiles_tutor_id', $appointment->Tutor_profiles_tutor_id)
                            ->orWhere('Student_profiles_student_id', $appointment->Student_profiles_student_id);
                    })->exists();
                if ($conflict) {
                    throw ValidationException::withMessages(['status' => 'เวลาทับกับนัดหมายที่ยืนยันแล้ว กรุณาปฏิเสธคำขอหรือตกลงเวลาใหม่']);
                }
            }
            $appointment->update(['status' => $status]);
            $message = $status === 'confirmed' ? 'ติวเตอร์ยืนยันคำขอเรียนแล้ว' : 'ติวเตอร์ปฏิเสธคำขอเรียน';
            $this->notify($appointment->studentProfile->user_id, $message.' รหัส '.$appointment->Appointment_id, 'BOOKANS001', 'ผลคำขอจองเรียน');
        });

        return back()->with('success', $status === 'confirmed' ? 'ยืนยันคำขอแล้ว นักเรียนดูรายละเอียดในตารางนัดหมายได้' : 'ปฏิเสธคำขอแล้ว');
    }

    private function notify(string $userId, string $message, string $typeId, string $typeName): void
    {
        // notification_types เป็นตารางเดิม สร้างเฉพาะประเภทที่ยังไม่มี
        NotificationType::firstOrCreate(['notification_type_id' => $typeId], ['type_name' => $typeName]);
        Notification::create([
            'notification_id' => strtoupper(Str::random(10)), 'message' => $message,
            'is_read' => false, 'Users_user_id' => $userId,
            'NotificationType_notification_type_id' => $typeId,
        ]);
    }
}
