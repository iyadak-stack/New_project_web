<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Models\Contact;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function edit(Request $request, string $studentId)
    {
        $this->checkOwner($request, $studentId);

        // ใช้ฟอร์มโปรไฟล์นักเรียนที่มีอยู่ ไม่เรียก view ที่ไม่มีจริง
        return redirect()->route('student.profile.edit');
    }

    public function update(Request $request, string $studentId)
    {
        $this->checkOwner($request, $studentId);
        $data = $request->validate([
            'line_id' => ['nullable', 'string', 'max:45'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'google_meet_link' => ['nullable', 'url:http,https', 'max:255'],
            'zoom_link' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        // contacts เชื่อมผ่าน Users_user_id ไม่ใช่ student_id
        $contact = Contact::firstOrNew(['Users_user_id' => $request->user()->user_id]);
        if (! $contact->exists) {
            $contact->contact_id = strtoupper(Str::random(10));
        }
        $contact->fill($data);
        $contact->save();

        return back()->with('success', 'บันทึกข้อมูลติดต่อสำเร็จ');
    }

    private function checkOwner(Request $request, string $studentId): void
    {
        $student = StudentProfile::where('id', $studentId)
            ->orWhere('user_id', $studentId)->firstOrFail();
        abort_unless((string) $student->user_id === (string) $request->user()->user_id, 403);
    }
}
