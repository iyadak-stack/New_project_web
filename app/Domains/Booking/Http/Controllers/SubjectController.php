<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // แสดงรายการวิชาทั้งหมด
    public function index()
    {
        $subjects = Subject::all();
        return view('domains.booking.subjects.index', compact('subjects'));
    }

    // บันทึกวิชาใหม่
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|unique:subjects,subject_id',
            'subject_name' => 'required|string|max:45',
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
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->back()->with('success', 'ลบวิชาเรียบร้อยแล้ว');
    }
}