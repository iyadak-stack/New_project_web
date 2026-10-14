<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Auth\Models\Contact;
use App\Domains\Booking\Models\Subject;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TutorTeachingController extends Controller
{
    public function index(Request $request)
    {
        $tutor = $request->user()->tutorProfile()->with('subjects')->firstOrFail();
        $subjects = Subject::orderBy('subject_name')->get();
        $contact = $request->user()->contact;

        return view('domains.tutor-profile.tutor.teaching', compact('tutor', 'subjects', 'contact'));
    }

    // หน้าแยกสำหรับจัดเวลารับคำขอเรียนของติวเตอร์ที่ล็อกอิน
    public function schedule(Request $request)
    {
        $availabilities = Availability::where('user_id', $request->user()->user_id)
            ->where('end_datetime', '>=', now('Asia/Bangkok')->format('Y-m-d H:i:s'))
            ->orderBy('start_datetime')->get();

        return view('domains.tutor-profile.tutor.schedule', compact('availabilities'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['string', 'distinct', 'exists:subjects,subject_id'],
            'teaching_mode' => ['required', 'in:online,onsite,both'],
            'line_id' => ['nullable', 'string', 'max:45'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'zoom_link' => ['nullable', 'url', 'starts_with:http://,https://', 'max:255'],
            'google_meet_link' => ['nullable', 'url', 'starts_with:http://,https://', 'max:255'],
        ]);
        $tutor = $request->user()->tutorProfile()->firstOrFail();

        // บันทึกรูปแบบ วิชา และช่องทางเรียนพร้อมกัน ไม่สร้างรายวิชากลางใหม่
        DB::transaction(function () use ($request, $data, $tutor) {
            $tutor->update(['teaching_mode' => $data['teaching_mode']]);
            $tutor->subjects()->sync($data['subjects'] ?? []);

            $contact = Contact::firstOrNew(['Users_user_id' => $request->user()->user_id]);
            if (! $contact->exists) {
                $contact->contact_id = strtoupper(Str::random(10));
            }
            $contact->line_id = $data['line_id'] ?? null;
            $contact->discord_id = $data['discord_id'] ?? null;
            $contact->zoom_link = $data['zoom_link'] ?? null;
            $contact->google_meet_link = $data['google_meet_link'] ?? null;
            $contact->save();
        });

        return back()->with('success', 'บันทึกวิชาและข้อมูลการสอนแล้ว');
    }

    public function addAvailability(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
        $start = Carbon::parse($data['date'].' '.$data['start_time'], 'Asia/Bangkok');
        $end = Carbon::parse($data['date'].' '.$data['end_time'], 'Asia/Bangkok');
        if (! $start->isFuture()) {
            throw ValidationException::withMessages(['date' => 'กรุณาเลือกเวลาว่างในอนาคต']);
        }

        DB::transaction(function () use ($request, $start, $end) {
            TutorProfile::where('Users_user_id', $request->user()->user_id)->lockForUpdate()->firstOrFail();
            $overlap = Availability::where('user_id', $request->user()->user_id)
                ->where('start_datetime', '<', $end->format('Y-m-d H:i:s'))
                ->where('end_datetime', '>', $start->format('Y-m-d H:i:s'))->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_time' => 'ช่วงเวลานี้ทับกับเวลาว่างที่มีอยู่แล้ว']);
            }
            // availabilities ใช้ user_id ของผู้ใช้ ไม่ใช่ tutor_id
            Availability::create([
                'availability_id' => strtoupper(Str::random(10)),
                'user_id' => $request->user()->user_id,
                'start_datetime' => $start->format('Y-m-d H:i:s'),
                'end_datetime' => $end->format('Y-m-d H:i:s'),
            ]);
        });

        return back()->with('success', 'เพิ่มเวลาว่างสำหรับรับคำขอเรียนแล้ว');
    }

    public function deleteAvailability(Request $request, string $id)
    {
        $slot = Availability::findOrFail($id);
        abort_unless($slot->user_id === $request->user()->user_id, 403);
        $slot->delete();

        // ลบเฉพาะเวลารับคำขอใหม่ นัดหมายที่บันทึกไว้ยังอยู่ตามเดิม
        return back()->with('success', 'นำช่วงเวลานี้ออกแล้ว นัดหมายเดิมไม่ได้ถูกยกเลิก');
    }
}
