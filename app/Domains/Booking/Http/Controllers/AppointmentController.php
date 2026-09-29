<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Booking\Models\Notification;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // แสดงรายการนัดหมายทั้งหมด
    public function index()
    {
        $appointments = Appointment::with('subject')->orderBy('created_at', 'desc')->get();
        return view('domains.booking.appointments.index', compact('appointments'));
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
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'tutor_id' => 'required',
            'student_id' => 'required',
        ]);

        // สุ่ม ID ง่ายๆ แบบนักศึกษาทำ
        $appointmentId = 'APP' . rand(100, 999);

        $appointment = Appointment::create([
            'Appointment_id' => $appointmentId,
            'status' => 'pending',
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'Subject_subject_id' => $request->subject_id,
            'Tutor_profiles_tutor_id' => $request->tutor_id,
            'Student_profiles_student_id' => $request->student_id,
        ]);

        // แจ้งเตือนไปยัง ติวเตอร์
        Notification::create([
            'notification_id' => 'NOT' . rand(1000, 9999),
            'message' => 'มีการจองนัดหมายใหม่รอยืนยัน',
            'is_read' => 0,
            'Users_user_id' => $request->tutor_id,
            'NotificationType_notification_type_id' => 'TYPE01', // สมมุติตัวอย่าง ID ประเภทแจ้งเตือน
        ]);

        return redirect()->route('appointments.index')->with('success', 'สร้างรายการนัดหมายสำเร็จ');
    }

    // ดูรายละเอียดนัดหมาย
    public function show($id)
    {
        $appointment = Appointment::with('subject')->findOrFail($id);
        return view('domains.booking.appointments.show', compact('appointment'));
    }

    // อัปเดตสถานะ (ยืนยัน / เลื่อน / ยกเลิก)[cite: 2]
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status; // 'confirmed', 'cancelled'
        $appointment->save();

        // แจ้งเตือนเปลี่ยนสถานะ
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