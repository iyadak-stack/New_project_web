<?php

namespace App\Http\Controllers;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\Reportreview\Models\Review;
use App\Domains\Reportreview\Services\ReportNotification;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    // 1. สรุปข้อมูลจริง ไม่สร้างข้อมูลตัวเลขจำลองใน View
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'students' => User::whereHas('studentProfile')->count(),
            'tutors' => User::whereHas('tutorProfile')->count(),
            'appointments' => Appointment::count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
            'suspended' => User::where('is_active', false)->count(),
        ];
        $latestReports = Report::with(['reason', 'reporter', 'tutorProfile.user'])->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'latestReports'));
    }

    // 2. ค้นหาและพัก/เปิดใช้งานบัญชี โดยไม่เปลี่ยนบทบาทหรือรหัสผ่าน
    public function users(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:student,tutor,admin'],
            'active' => ['nullable', 'in:0,1'],
        ]);
        // เริ่มจากผู้ใช้ทั้งหมด แล้วค่อยเพิ่มเงื่อนไขตามช่องที่กรอก
        $query = User::with(['studentProfile', 'tutorProfile']);

        if ($request->filled('search')) {
            $search = $filters['search'];
            // ครอบ OR ไว้ในวงเดียวกัน เพื่อไม่ให้ข้ามตัวกรองประเภทและสถานะ
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = $filters['role'];
            if ($role === 'admin') {
                $query->where(function ($query) {
                    $query->where('role', 'admin')->orWhere('current_role', 'admin');
                });
            } else {
                // ผู้ใช้มีได้ทั้งโปรไฟล์นักเรียนและติวเตอร์
                if ($role === 'tutor') {
                    $profile = 'tutorProfile';
                } else {
                    $profile = 'studentProfile';
                }

                $query->where('role', '!=', 'admin')->where('current_role', '!=', 'admin')
                    ->where(function ($query) use ($role, $profile) {
                        $query->where('role', $role)
                            ->orWhere('current_role', $role)
                            ->orWhereHas($profile);
                    });
            }
        }

        // filled ใช้กับค่า "0" ได้ จึงกรองบัญชีที่ถูกพักได้ด้วย
        if ($request->filled('active')) {
            $query->where('is_active', $filters['active']);
        }

        // แสดงทีละ 12 คน และเก็บตัวกรองไว้เมื่อกดหน้าถัดไป
        $users = $query->latest()->paginate(12)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function user(string $id)
    {
        $member = User::with(['studentProfile', 'tutorProfile.subjects', 'contact'])->findOrFail($id);

        return view('admin.user', compact('member'));
    }

    public function updateUserStatus(Request $request, string $id)
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        // transaction: ถ้าบันทึกไม่สำเร็จ จะยกเลิกการเปลี่ยนแปลงในชุดนี้
        // lockForUpdate: กันการแก้บัญชีเดียวกันพร้อมกันระหว่างบันทึก
        DB::transaction(function () use ($request, $id, $data) {
            $member = User::lockForUpdate()->findOrFail($id);
            abort_if($member->user_id === $request->user()->user_id || $member->role === 'admin' || $member->current_role === 'admin', 403);
            $member->update(['is_active' => $data['is_active']]);
        });

        return back()->with('success', $data['is_active'] ? 'เปิดใช้งานบัญชีแล้ว' : 'พักบัญชีแล้ว');
    }

    // 3. ใช้ status และ updated_at เดิม เก็บเฉพาะผลล่าสุด
    public function updateReport(Request $request, string $id)
    {
        $data = $request->validate(['status' => ['required', 'in:pending,in_progress,resolved']]);
        DB::transaction(function () use ($id, $data) {
            $report = Report::lockForUpdate()->findOrFail($id);

            // ถ้าสถานะยังเหมือนเดิม ไม่ต้องส่งแจ้งเตือนซ้ำ
            if ($report->status === $data['status']) {
                return;
            }
            $report->update(['status' => $data['status']]);

            $statusNames = [
                'pending' => 'รอตรวจสอบ',
                'in_progress' => 'กำลังตรวจสอบ',
                'resolved' => 'ตรวจสอบเสร็จแล้ว',
            ];
            // แจ้งเจ้าของรายงานเท่านั้น ถ้าบัญชีถูกลบแล้วให้ข้าม
            $reporter = $report->reporter;
            if ($reporter && ! $reporter->trashed()) {
                ReportNotification::send($reporter->user_id, 'รายงานรหัส '.$report->Report_id.' เปลี่ยนสถานะเป็น '.$statusNames[$data['status']]);
            }
        });

        return back()->with('success', 'บันทึกสถานะรายงานล่าสุดแล้ว');
    }

    public function reasons()
    {
        $reasons = ReportReason::withCount('reports')->orderBy('Reason_id')->get();

        return view('admin.reasons', compact('reasons'));
    }

    public function saveReason(Request $request, ?string $id = null)
    {
        // ชื่อต้องไม่ซ้ำ แต่ตอนแก้ไขให้ใช้ชื่อเดิมของรายการนั้นได้
        $data = $request->validate([
            'reason_name' => ['required', 'string', 'max:45', Rule::unique('report_reasons')->ignore($id, 'Reason_id')],
        ]);
        if ($id) {
            ReportReason::findOrFail($id)->update($data);
        } else {
            $data['Reason_id'] = strtoupper(Str::random(10));
            ReportReason::create($data);
        }

        return back()->with('success', 'บันทึกประเภทการรายงานแล้ว');
    }

    public function deleteReason(string $id)
    {
        DB::transaction(function () use ($id) {
            $reason = ReportReason::lockForUpdate()->findOrFail($id);
            if ($reason->reports()->exists()) {
                throw ValidationException::withMessages(['reason' => 'ประเภทนี้มีรายงานใช้งานอยู่ จึงไม่สามารถลบได้']);
            }
            $reason->delete();
        });

        return back()->with('success', 'ลบประเภทการรายงานที่ไม่ได้ใช้งานแล้ว');
    }

    // 4. วิชาที่เชื่อมกับผู้ใช้/นัดหมาย/รายการโปรดอยู่ จะไม่อนุญาตให้ลบ
    public function subjects(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $query = Subject::withCount('tutors');
        if ($request->filled('search')) {
            $query->where('subject_name', 'like', '%'.$request->input('search').'%');
        }
        $subjects = $query->orderBy('subject_name')->paginate(12)->withQueryString();

        return view('admin.subjects', compact('subjects'));
    }

    public function saveSubject(Request $request, ?string $id = null)
    {
        $data = $request->validate([
            'subject_name' => ['required', 'string', 'max:45', Rule::unique('subjects')->ignore($id, 'subject_id')],
        ]);
        if ($id) {
            Subject::findOrFail($id)->update($data);
        } else {
            $data['subject_id'] = strtoupper(Str::random(10));
            Subject::create($data);
        }

        return back()->with('success', 'บันทึกรายวิชาแล้ว');
    }

    public function deleteSubject(string $id)
    {
        DB::transaction(function () use ($id) {
            $subject = Subject::lockForUpdate()->findOrFail($id);
            foreach (['appointments', 'favorites', 'tutor_profiles_has_subjects', 'Student_profiles_has_Subject'] as $table) {
                if (DB::table($table)->where('Subject_subject_id', $id)->exists()) {
                    throw ValidationException::withMessages(['subject' => 'วิชานี้มีข้อมูลเชื่อมอยู่ กรุณาแก้ไขชื่อแทนการลบ']);
                }
            }
            $subject->delete();
        });

        return back()->with('success', 'ลบวิชาที่ไม่ได้ใช้งานแล้ว');
    }

    // 5. ซ่อนรีวิวด้วย deleted_at เดิม และเปิดคืนได้ ไม่สร้างตารางประวัติ
    public function reviews(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'visibility' => ['nullable', 'in:visible,hidden'],
        ]);

        // withTrashed ทำให้แอดมินดูและเปิดคืนรีวิวที่ซ่อนไว้ได้
        $query = Review::withTrashed()->with(['tutorProfile.user', 'appointment.studentProfile.user']);
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }
        if ($request->input('visibility') === 'visible') {
            $query->whereNull('deleted_at');
        } elseif ($request->input('visibility') === 'hidden') {
            $query->whereNotNull('deleted_at');
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('tutorProfile.user', function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        $reviews = $query->latest()->paginate(12)->withQueryString();

        return view('admin.reviews', compact('reviews'));
    }

    public function updateReviewVisibility(Request $request, string $id)
    {
        $data = $request->validate(['visible' => ['required', 'boolean']]);
        DB::transaction(function () use ($id, $data) {
            $review = Review::withTrashed()->lockForUpdate()->findOrFail($id);
            // ล็อกโปรไฟล์ด้วย เพื่อให้คะแนนเฉลี่ยถูกต้องเมื่อจัดการหลายรีวิวพร้อมกัน
            TutorProfile::where('tutor_id', $review->Tutor_profiles_tutor_id)->lockForUpdate()->first();
            if ($data['visible']) {
                $review->restore();
            } else {
                $review->delete();
            }
            // คะแนนโปรไฟล์ต้องคำนวณจากรีวิวที่ยังแสดงอยู่เท่านั้น
            $average = Review::where('Tutor_profiles_tutor_id', $review->Tutor_profiles_tutor_id)->avg('rating') ?? 0;
            TutorProfile::where('tutor_id', $review->Tutor_profiles_tutor_id)->update(['average_rating' => round($average, 2)]);
        });

        return back()->with('success', $data['visible'] ? 'แสดงรีวิวอีกครั้งแล้ว' : 'นำรีวิวออกจากการแสดงผลแล้ว');
    }

    // 6. แอดมินตรวจนัดหมายได้ แต่ไม่เปลี่ยนการจองของผู้ใช้จากหน้านี้
    public function appointments(Request $request)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,confirmed,cancelled,completed'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $query = Appointment::with(['subject', 'studentProfile.user', 'tutorProfile.user']);
        if ($request->filled('status')) {
            $query->where('status', $data['status']);
        }
        if ($request->filled('date')) {
            $query->whereDate('start_datetime', $data['date']);
        }
        if ($request->filled('search')) {
            $search = $data['search'];
            // ค้นหาจากผู้เรียน ติวเตอร์ หรือชื่อวิชา ภายในกลุ่มเงื่อนไขเดียวกัน
            $query->where(function ($query) use ($search) {
                $query->whereHas('studentProfile.user', function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('tutorProfile.user', function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('subject', function ($query) use ($search) {
                    $query->where('subject_name', 'like', "%{$search}%");
                });
            });
        }
        $appointments = $query->orderByDesc('start_datetime')->paginate(12)->withQueryString();

        return view('admin.appointments', compact('appointments'));
    }
}
