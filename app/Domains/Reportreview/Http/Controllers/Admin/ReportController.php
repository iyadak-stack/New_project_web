<?php

namespace App\Domains\Reportreview\Http\Controllers\Admin;

use App\Domains\Reportreview\Models\Report;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'investigating', 'resolved', 'rejected'])],
            'reason' => ['nullable', 'string', 'exists:report_reasons,Reason_id'],
        ]);

        $reports = Report::with(['reporter', 'reason', 'review'])
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('Report_id', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('review_id', 'like', "%{$search}%")
                        ->orWhereHas('reporter', fn ($user) => $user->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['reason'] ?? null, fn ($query, $reason) => $query->where('ReportReason_Reason_id', $reason))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('domains.reportreview.admin.reports.index', [
            'reports' => $reports,
            'reasons' => \App\Domains\Reportreview\Models\ReportReason::orderBy('reason_name')->get(),
        ]);
    }

    public function show(Report $report)
    {
        $report->load([
            'reporter',
            'reason',
            'review.appointment.studentProfile.user',
            'review.tutorProfile.user',
            'handler',
            'resolver',
        ]);

        return view('domains.reportreview.admin.reports.show', compact('report'));
    }

    public function investigate(Request $request, Report $report)
    {
        abort_unless($report->status === 'pending', 409, 'รายงานนี้ไม่ได้อยู่ในสถานะรอตรวจสอบ');

        $report->update([
            'status' => 'investigating',
            'handled_by' => $request->user()->user_id,
            'handled_at' => now(),
        ]);

        return back()->with('success', 'รับรายงานเพื่อตรวจสอบแล้ว');
    }

    public function resolve(Request $request, Report $report)
    {
        abort_unless(in_array($report->status, ['pending', 'investigating'], true), 409, 'รายงานนี้ปิดดำเนินการแล้ว');

        $validated = $request->validate([
            'delete_review' => ['nullable', 'boolean'],
        ]);

        if (($validated['delete_review'] ?? false) && ! $report->review) {
            throw ValidationException::withMessages([
                'delete_review' => 'รายงานนี้ไม่มีรีวิวที่เชื่อมโยงให้ลบ',
            ]);
        }

        DB::transaction(function () use ($request, $report, $validated) {
            if ($validated['delete_review'] ?? false) {
                $report->review->delete();
            }

            $report->update([
                'status' => 'resolved',
                'handled_by' => $request->user()->user_id,
                'handled_at' => now(),
                'resolved_by' => $request->user()->user_id,
                'resolved_at' => now(),
            ]);
        });

        return back()->with('success', 'ดำเนินการกับรายงานเรียบร้อยแล้ว');
    }

    public function reject(Request $request, Report $report)
    {
        abort_unless(in_array($report->status, ['pending', 'investigating'], true), 409, 'รายงานนี้ปิดดำเนินการแล้ว');

        $report->update([
            'status' => 'rejected',
            'handled_by' => $request->user()->user_id,
            'handled_at' => now(),
            'resolved_by' => $request->user()->user_id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'ปฏิเสธรายงานแล้ว โดยรีวิวยังคงอยู่ในระบบ');
    }
}
