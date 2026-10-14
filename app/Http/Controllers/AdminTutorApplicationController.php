<?php

namespace App\Http\Controllers;

use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Services\TutorApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminTutorApplicationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', 'in:pending,approved,rejected']]);
        $status = $data['status'] ?? 'pending';
        $applications = TutorProfile::with('user')->whereHas('user')
            ->where('approval_status', $status)->orderByDesc('submitted_at')->paginate(12)->withQueryString();

        return view('admin.tutor-applications', compact('applications', 'status'));
    }

    public function show(string $id)
    {
        $application = TutorProfile::with(['user.contact', 'subjects'])->whereHas('user')->findOrFail($id);

        return view('admin.tutor-application', compact('application'));
    }

    public function decide(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'reject_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ]);
        DB::transaction(function () use ($id, $data) {
            $profile = TutorProfile::lockForUpdate()->findOrFail($id);
            if ($profile->approval_status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'คำขอนี้ไม่ได้อยู่ระหว่างรอตรวจสอบ']);
            }
            if (! $profile->user || ! $profile->user->is_active) {
                throw ValidationException::withMessages(['status' => 'บัญชีนี้ถูกลบหรือพักการใช้งาน']);
            }
            $profile->update([
                'approval_status' => $data['status'],
                'reject_reason' => $data['status'] === 'rejected' ? $data['reject_reason'] : null,
            ]);
            $message = $data['status'] === 'approved'
                ? 'คำขอเป็นติวเตอร์ได้รับอนุมัติแล้ว สามารถสลับบทบาทเป็นติวเตอร์ได้'
                : 'คำขอเป็นติวเตอร์ไม่ผ่าน กรุณาดูเหตุผลและแก้ไขข้อมูลในหน้าคำขอ';
            TutorApplicationNotification::send($profile->Users_user_id, $message);
        });

        return back()->with('success', 'บันทึกผลตรวจคำขอแล้ว');
    }
}
