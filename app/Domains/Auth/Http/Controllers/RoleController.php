<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\TutorProfile\Models\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function switchRole(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:tutor,student'],
        ]);

        $user = $request->user();
        $role = $validated['role'];

        if ($role === 'tutor') {
            // ยังไม่อนุมัติ: ให้กรอกใบสมัครก่อน และยังใช้บทบาทนักเรียน
            if ($user->tutorProfile()->first()?->approval_status !== 'approved') {
                return redirect()->route('tutor.application.index');
            }
        }

        if ($role === 'student') {
            StudentProfile::firstOrCreate([
                'user_id' => $user->user_id,
            ]);
            $user->load('studentProfile');
        }

        // บันทึกลง Database
        $user->current_role = $role;
        $user->save();
        $user->refresh();

        // บันทึก Role ปัจจุบันลง Session เพื่อให้ Navbar เช็กค่าได้ตลอดทุกหน้า
        session(['active_role' => $role]);

        $message = $role === 'tutor'
            ? 'สลับเป็นโหมดติวเตอร์เรียบร้อยแล้ว'
            : 'สลับเป็นโหมดนักเรียนเรียบร้อยแล้ว';

        // เปิดหน้าแรกใหม่ตามบทบาทที่บันทึกแล้ว ไม่ย้อนกลับไปหน้าเดิมของอีกบทบาท
        return redirect()->route('home')->with('success', $message);
    }
}