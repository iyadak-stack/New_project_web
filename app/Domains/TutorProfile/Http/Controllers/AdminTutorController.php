<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Booking\Services\UserNotification;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminTutorController extends Controller
{
    public function index()
    {
        // ให้ใบสมัครที่รอตรวจสอบอยู่ด้านบน
        $applications = TutorProfile::with(['user.contact', 'subjects'])
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 ELSE 1 END")
            ->latest('updated_at')
            ->paginate(20);

        return view('domains.reportreview.admin.tutors', compact('applications'));
    }

    public function update(Request $request, TutorProfile $tutorProfile)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'reject_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ]);
        // ล็อกใบสมัครเพื่อป้องกันแอดมินสองคนบันทึกผลทับกัน
        DB::transaction(function () use ($request, $tutorProfile, $data) {
            $profile = TutorProfile::whereKey($tutorProfile->tutor_id)->lockForUpdate()->firstOrFail();
            abort_unless($profile->approval_status === 'pending', 409, 'ใบสมัครนี้ได้รับการตรวจสอบแล้ว');
            abort_if($profile->Users_user_id === $request->user()->user_id, 403, 'ไม่สามารถอนุมัติใบสมัครของตัวเองได้');
            if ($data['decision'] === 'approved' && ! $profile->isComplete()) {
                throw ValidationException::withMessages(['decision' => 'ข้อมูลติวเตอร์ยังไม่ครบ กรุณาให้ผู้สมัครแก้ไขข้อมูลก่อน']);
            }
            // เก็บว่าใครตรวจใบสมัคร และตรวจเมื่อไร
            $profile->approval_status = $data['decision'];
            $profile->reviewed_at = now();
            $profile->reviewed_by = $request->user()->user_id;

            if ($data['decision'] === 'approved') {
                $profile->reject_reason = null;
                $message = 'ใบสมัครติวเตอร์ได้รับอนุมัติแล้ว สามารถสลับบทบาทเพื่อเริ่มสอนได้';
            } else {
                $profile->reject_reason = $data['reject_reason'];
                $message = 'ใบสมัครติวเตอร์ไม่ผ่านการตรวจสอบ: '.$data['reject_reason'];
            }

            $profile->save();
            UserNotification::send($profile->Users_user_id, $message);
        });

        return back()->with('success', 'บันทึกผลตรวจสอบและแจ้งผู้สมัครแล้ว');
    }
}
