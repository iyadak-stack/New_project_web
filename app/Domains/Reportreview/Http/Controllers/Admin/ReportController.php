<?php

namespace App\Domains\Reportreview\Http\Controllers\Admin;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\Reportreview\Services\ReportNotification;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function create(Request $request)
    {
        // ฟอร์มรายงานอยู่ในหน้าโปรไฟล์ ไม่ต้องทำฟอร์มซ้ำอีกหน้า
        if ($request->filled('reported_tutor_id')) {
            return redirect()->route('tutor.show', $request->input('reported_tutor_id'));
        }

        return redirect()->route('tutor.search');
    }

    public function store(Request $request)
    {
        // ตรวจข้อมูลฝั่ง Server อีกครั้ง แม้ใน HTML จะมี required แล้ว
        $data = $request->validate([
            'reported_tutor_id' => ['required', 'string', 'exists:tutor_profiles,tutor_id'],
            'reason_type' => ['required', 'string', 'exists:report_reasons,Reason_id'],
            'description' => ['required', 'string', 'max:3000'],
        ], [
            'reported_tutor_id.exists' => 'ไม่พบติวเตอร์ที่ต้องการรายงาน',
            'reason_type.required' => 'กรุณาเลือกประเภทการรายงาน',
            'reason_type.exists' => 'ประเภทการรายงานไม่ถูกต้อง กรุณาเลือกใหม่',
            'description.required' => 'กรุณาระบุรายละเอียดปัญหา',
            'description.max' => 'รายละเอียดต้องไม่เกิน 3,000 ตัวอักษร',
        ]);

        $tutor = TutorProfile::whereHas('user')->findOrFail($data['reported_tutor_id']);
        $isApproved = empty($tutor->approval_status) || $tutor->approval_status === 'approved';
        abort_unless($isApproved || $tutor->Users_user_id === $request->user()->user_id, 404);

        // ใช้ผู้ส่งจากบัญชีที่ล็อกอิน ป้องกันการส่ง user_id ของคนอื่นมาจากฟอร์ม
        // pending คือรอแอดมินตรวจสอบ และจะปรากฏในหน้ารายงานแอดมินทันที
        // transaction ช่วยให้รายงานกับแจ้งเตือนบันทึกสำเร็จพร้อมกัน
        $report = DB::transaction(function () use ($request, $data, $tutor) {
            $report = Report::create([
                'Report_id' => strtoupper(Str::random(10)),
                'description' => $data['description'],
                'status' => 'pending',
                'ReportReason_Reason_id' => $data['reason_type'],
                'Users_user_id' => $request->user()->user_id,
                'Tutor_profiles_tutor_id' => $tutor->tutor_id,
            ]);

            ReportNotification::send($request->user()->user_id, 'ส่งรายงานแล้ว รหัส '.$report->Report_id.' รอแอดมินตรวจสอบ');

            // ส่งให้บัญชีแอดมินที่ใช้งานอยู่ ไม่ส่งให้ติวเตอร์ที่ถูกรายงาน
            $admins = User::where('is_active', true)->where(function ($query) {
                $query->where('role', 'admin')->orWhere('current_role', 'admin');
            })->get();
            foreach ($admins as $admin) {
                ReportNotification::send($admin->user_id, 'มีรายงานใหม่ รหัส '.$report->Report_id.' กรุณาตรวจสอบ');
            }

            return $report;
        });

        return redirect()->route('tutor.show', $tutor->tutor_id)
            ->with('success', 'ส่งรายงานเรียบร้อยแล้ว รหัสรายงาน '.$report->Report_id.' อยู่ในรายการรอแอดมินตรวจสอบ');
    }

    public function index(Request $request)
    {
        // ตรวจบทบาทจากบัญชีจริง ไม่ใช้ค่าที่ผู้ใช้ส่งมาหรือ active_role ใน Session
        $user = $request->user();
        abort_unless($user->role === 'admin' || $user->current_role === 'admin', 403);

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,in_progress,resolved'],
            'reason' => ['nullable', 'string', 'exists:report_reasons,Reason_id'],
        ]);

        // เพิ่มตัวกรองทีละช่อง เหมือนหน้าผู้ใช้และหน้านัดหมาย
        $query = Report::with(['reason', 'reporter', 'tutorProfile.user']);
        if ($request->filled('status')) {
            $query->where('status', $data['status']);
        }
        if ($request->filled('reason')) {
            $query->where('ReportReason_Reason_id', $data['reason']);
        }
        if ($request->filled('search')) {
            $search = $data['search'];
            $query->where(function ($query) use ($search) {
                $query->where('Report_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tutorProfile.user', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%");
                    });
            });
        }
        $reports = $query->orderByDesc('created_at')->paginate(12)->withQueryString();
        $reasons = ReportReason::orderBy('Reason_id')->get();

        return view('domains.reportreview.admin.reports.index', compact('reports', 'reasons'));
    }
}
