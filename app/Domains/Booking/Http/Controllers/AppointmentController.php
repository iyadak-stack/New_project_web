<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Booking\Models\Notification;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // แสดงรายการนัดหมายทั้งหมด (พร้อมรองรับการกรองสถานะตามแท็บ)
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status', 'all');

        // ดึงข้อมูลนัดหมายพร้อม Relation (วิชา, ติวเตอร์, นักเรียน)
        $query = Appointment::with([
            'subject',
            'studentProfile.user',
            'tutorProfile.user'
        ]);

        // กรองนัดหมายตามบทบาทใน Session ปัจจุบัน
        if ($user->current_role === 'tutor') {
            $tutorId = $user->tutorProfile->tutor_id ?? null;
            $query->where('Tutor_profiles_tutor_id', $tutorId);
        } else {
            $studentId = $user->studentProfile->student_id ?? null;
            $query->where('Student_profiles_student_id', $studentId);
        }

        // กรองสถานะเมื่อกดเลือกจากแท็บ (pending, confirmed, cancelled)
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $appointments = $query->orderBy('start_datetime', 'desc')->get();

        return view('domains.booking.appointments.index', compact('appointments', 'status'));
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
        $request->validate([
            'subject_id' => 'required',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'tutor_id' => 'required',
            'teaching_mode' => 'required|in:online,onsite',
        ]);

        $user = auth()->user();
        $studentId = $user->studentProfile->student_id ?? $request->student_id;

        // คำนวณเวลา start_datetime และ end_datetime จากตัวเลือกเวลา
        $date = $request->appointment_date;
        $times = explode('-', $request->appointment_time);
        $startTime = isset($times[0]) ? trim($times[0]) : '09:00';
        $endTime = isset($times[1]) ? trim($times[1]) : '11:00';

        $startDatetime = $date . ' ' . $startTime . ':00';
        $endDatetime = $date . ' ' . $endTime . ':00';

        // สร้างรหัสนัดหมาย
        $appointmentId = 'APP' . rand(100, 999);

        $appointment = Appointment::create([
            'Appointment_id' => $appointmentId,
            'status' => 'pending',
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'teaching_mode' => $request->teaching_mode,
            'note' => $request->note,
            'Subject_subject_id' => $request->subject_id,
            'Tutor_profiles_tutor_id' => $request->tutor_id,
            'Student_profiles_student_id' => $studentId,
        ]);

        // ส่งแจ้งเตือนไปยังติวเตอร์
        Notification::create([
            'notification_id' => 'NOT' . rand(1000, 9999),
            'message' => 'คุณได้รับคำขอจองเรียนใหม่รอการตรวจสอบ',
            'is_read' => 0,
            'Users_user_id' => $request->tutor_id,
            'NotificationType_notification_type_id' => 'TYPE01',
        ]);

        return redirect()->route('appointments.index')->with('success', 'ส่งคำขอจองเรียนเรียบร้อยแล้ว');
    }

    // ดูรายละเอียดนัดหมาย
    public function show($id)
    {
        $appointment = Appointment::with([
            'subject',
            'studentProfile.user',
            'tutorProfile.user'
        ])->findOrFail($id);

        return view('domains.booking.appointments.show', compact('appointment'));
    }

    // ตอบรับคำขอจองเรียน (สำหรับติวเตอร์)
    public function accept($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = 'confirmed';
        $appointment->save();

        // แจ้งเตือนไปยังนักเรียน
        Notification::create([
            'notification_id' => 'NOT' . rand(1000, 9999),
            'message' => 'ติวเตอร์ได้ยืนยันคำขอจองเรียนของคุณแล้ว',
            'is_read' => 0,
            'Users_user_id' => $appointment->Student_profiles_student_id,
            'NotificationType_notification_type_id' => 'TYPE02',
        ]);

        return redirect()->back()->with('success', 'ตอบรับคำขอจองเรียนเรียบร้อยแล้ว');
    }

    // ปฏิเสธคำขอจองเรียน (สำหรับติวเตอร์)
    public function reject($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = 'cancelled';
        $appointment->save();

        // แจ้งเตือนไปยังนักเรียน
        Notification::create([
            'notification_id' => 'NOT' . rand(1000, 9999),
            'message' => 'คำขอจองเรียนของคุณถูกปฏิเสธ',
            'is_read' => 0,
            'Users_user_id' => $appointment->Student_profiles_student_id,
            'NotificationType_notification_type_id' => 'TYPE02',
        ]);

        return redirect()->back()->with('success', 'ปฏิเสธคำขอจองเรียนเรียบร้อยแล้ว');
    }

    // อัปเดตสถานะแบบกำหนดเอง[cite: 2]
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();

        Notification::create([
            'notification_id' => 'NOT' . rand(1000, 9999),
            'message' => 'สถานะการนัดหมายของคุณเปลี่ยนเป็น: ' . $request->status,
            'is_read' => 0,
            'Users_user_id' => $appointment->Student_profiles_student_id,
            'NotificationType_notification_type_id' => 'TYPE02',
        ]);

        return redirect()->back()->with('success', 'อัปเดตสถานะการนัดหมายแล้ว');
    }
}