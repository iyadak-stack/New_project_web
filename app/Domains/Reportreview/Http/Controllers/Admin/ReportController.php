<?php

namespace App\Domains\Reportreview\Http\Controllers\Admin;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(Request $request): View
    {
        $tutor = TutorProfile::query()
            ->with('user')
            ->where('tutor_id', $request->query('tutor_id'))
            ->firstOrFail();

        return view('domains.reportreview.reports.create', [
            'tutor' => $tutor,
            'reasons' => ReportReason::query()->orderBy('reason_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tutor_id' => ['required', 'exists:tutor_profiles,tutor_id'],
            'reason_id' => ['required', 'exists:report_reasons,Reason_id'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $tutor = TutorProfile::query()->where('tutor_id', $data['tutor_id'])->firstOrFail();

        if ((string) $tutor->Users_user_id === (string) $request->user()->user_id) {
            throw ValidationException::withMessages([
                'tutor_id' => 'ไม่สามารถรายงานโปรไฟล์ของตัวเองได้',
            ]);
        }

        do {
            $reportId = 'RPT'.Str::upper(Str::random(7));
        } while (Report::query()->whereKey($reportId)->exists());

        Report::create([
            'Report_id' => $reportId,
            'description' => $data['description'],
            'status' => 'pending',
            'ReportReason_Reason_id' => $data['reason_id'],
            'Users_user_id' => $request->user()->user_id,
            'Reported_users_user_id' => $tutor->Users_user_id,
        ]);

        return redirect()
            ->route('tutor.show', $tutor->tutor_id)
            ->with('report_success', 'ส่งรายงานให้ทีมงานตรวจสอบแล้ว');
    }

    public function dashboard(): View
    {
        return view('domains.reportreview.admin.dashboard', [
            'pendingReports' => Report::query()->where('status', 'pending')->count(),
            'totalReports' => Report::query()->count(),
            'totalUsers' => User::query()->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
        ]);
    }

    public function index(): View
    {
        $reports = Report::query()
            ->with(['reason', 'reporter', 'reportedUser'])
            ->latest('created_at')
            ->paginate(20);

        return view('domains.reportreview.admin.reports.index', compact('reports'));
    }

    public function show(Report $report): View
    {
        $report->load(['reason', 'reporter', 'reportedUser', 'tutorProfile']);

        return view('domains.reportreview.admin.reports.show', compact('report'));
    }

    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:investigating,resolved,rejected'],
        ]);

        $report->update(['status' => $data['status']]);

        return back()->with('success', 'อัปเดตสถานะรายงานแล้ว');
    }

    public function users(): View
    {
        $users = User::query()->orderBy('first_name')->paginate(25);

        return view('domains.reportreview.admin.users.index', compact('users'));
    }

    public function toggleUserStatus(Request $request, User $user): RedirectResponse
    {
        if ((string) $user->user_id === (string) auth()->id()) {
            return back()->with('error', 'ไม่สามารถปิดใช้งานบัญชี Admin ที่กำลังใช้งานอยู่ได้');
        }

        if ($user->is_active) {
            $data = $request->validate([
                'disabled_reason' => ['required', 'string', 'max:2000'],
            ], [
                'disabled_reason.required' => 'กรุณาระบุเหตุผลที่ปิดใช้งาน เพื่อแจ้งเจ้าของบัญชี',
            ]);
            $user->is_active = false;
            $user->disabled_reason = $data['disabled_reason'];
        } else {
            $user->is_active = true;
            $user->disabled_reason = null;
        }
        $user->save();

        return back()->with('success', $user->is_active ? 'เปิดใช้งานบัญชีแล้ว' : 'ปิดใช้งานบัญชีแล้ว');
    }
}
