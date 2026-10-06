<?php

namespace App\Domains\Reportreview\Http\Controllers;

use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\Reportreview\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function create(Review $review)
    {
        abort_if($review->appointment?->studentProfile?->user_id === request()->user()->user_id, 403);

        return view('domains.reportreview.reports.create', [
            'review' => $review->load(['appointment.studentProfile.user', 'tutorProfile.user']),
            'reasons' => ReportReason::orderBy('reason_name')->get(),
        ]);
    }

    public function store(Request $request, Review $review)
    {
        abort_if($review->appointment?->studentProfile?->user_id === $request->user()->user_id, 403);

        $validated = $request->validate([
            'ReportReason_Reason_id' => ['required', 'string', 'exists:report_reasons,Reason_id'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        Report::create([
            'Report_id' => strtoupper(Str::random(10)),
            'review_id' => $review->Review_id,
            'description' => $validated['description'] ?? '',
            'status' => 'pending',
            'ReportReason_Reason_id' => $validated['ReportReason_Reason_id'],
            'Users_user_id' => $request->user()->user_id,
        ]);

        return redirect()->route('reviews.index')->with('success', 'ส่งรายงานรีวิวเรียบร้อยแล้ว');
    }
}
