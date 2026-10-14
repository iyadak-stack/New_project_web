<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TutorApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if($user->role === 'admin' || $user->current_role === 'admin', 403);
        $tutorProfile = TutorProfile::with('subjects')->firstOrNew(['Users_user_id' => $user->user_id]);
        if ($tutorProfile->approval_status === 'approved') {
            return redirect()->route('home')->with('success', 'คุณได้รับอนุมัติแล้ว สามารถสลับบทบาทเป็นติวเตอร์ได้');
        }
        $allSubjects = Subject::orderBy('subject_name')->get();
        $contact = $user->contact;
        // ใช้ฟอร์มเดียวกับแก้โปรไฟล์ เปลี่ยนเพียงหัวข้อและปุ่มส่งคำขอ
        $isApplication = true;

        return view('domains.tutor-profile.tutor.edit-profile', compact('user', 'tutorProfile', 'allSubjects', 'contact', 'isApplication'));
    }
}
