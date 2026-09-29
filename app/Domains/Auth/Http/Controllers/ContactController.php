<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // แสดงฟอร์มกรอก/แก้ไขข้อมูลติดต่อ
    public function edit($studentId)
    {
        $contact = Contact::where('student_id', $studentId)->first();

        return view('student-contacts.edit', compact('contact', 'studentId'));
    }

    // บันทึกข้อมูลติดต่อ (สร้างใหม่ ถ้ามีอยู่แล้วให้แก้ไขแทน)
    public function update(Request $request, $studentId)
    {
        $validated = $request->validate([
            'line_id' => 'nullable|string|max:50',
            'discord_id' => 'nullable|string|max:50',
            'google_meet_link' => 'nullable|string|max:255',
            'zoom_link' => 'nullable|string|max:255',
        ]);

        Contact::updateOrCreate(
            ['student_id' => $studentId],
            $validated
        );

        return back()->with('success', 'บันทึกข้อมูลติดต่อสำเร็จ');
    }
}