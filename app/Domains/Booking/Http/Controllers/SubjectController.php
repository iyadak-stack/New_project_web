<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Domains\Booking\Models\Subject;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // แสดงรายการวิชาทั้งหมด
    public function index()
    {
        $subjects = Subject::all();

        return view('domains.booking.subjects.index', compact('subjects'));
    }

    // แสดงรายละเอียดวิชาพร้อมรายชื่อติวเตอร์ที่สอนวิชานี้
    public function show($id)
    {
        // หน้าแสดงวิชาให้เห็นเฉพาะติวเตอร์ที่ผ่านการอนุมัติแล้ว
        $subject = Subject::with(['tutors' => function ($query) {
            $query->approved()->forStudentList()->with('user');
        }])->findOrFail($id);

        return view('domains.booking.subjects.show', compact('subject'));
    }

    // บันทึกวิชาใหม่
    public function store(Request $request)
    {
        $this->checkAdmin($request);
        $request->validate([
            'subject_id' => 'required|string|max:10|unique:subjects,subject_id',
            'subject_name' => 'required|string|max:45|unique:subjects,subject_name',
        ]);

        Subject::create([
            'subject_id' => $request->subject_id,
            'subject_name' => $request->subject_name,
        ]);

        return redirect()->back()->with('success', 'เพิ่มวิชาเรียบร้อยแล้ว');
    }

    // แก้ไขวิชา
    public function update(Request $request, $id)
    {
        $this->checkAdmin($request);
        $request->validate([
            'subject_name' => 'required|string|max:45',
        ]);

        $subject = Subject::findOrFail($id);
        $subject->update([
            'subject_name' => $request->subject_name,
        ]);

        return redirect()->back()->with('success', 'แก้ไขวิชาเรียบร้อยแล้ว');
    }

    // ลบวิชา
    public function destroy(Request $request, $id)
    {
        $this->checkAdmin($request);

        // ใช้กฎเดียวกับหน้าแอดมิน ไม่ลบวิชาที่ผูกกับนัดหมายหรือโปรไฟล์
        return app(AdminController::class)->deleteSubject($id);
    }

    private function checkAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'admin' || $user->current_role === 'admin'), 403);
    }
}
