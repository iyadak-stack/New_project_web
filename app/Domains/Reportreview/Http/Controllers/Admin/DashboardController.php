<?php

namespace App\Domains\Reportreview\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\Review;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('domains.reportreview.admin.dashboard', [
            'reviewCount' => Review::count(),
            'reportCount' => Report::count(),
            'pendingReportCount' => Report::where('status', 'pending')->count(),
            'recentReports' => Report::with(['reporter', 'reason'])->latest()->limit(5)->get(),
            'recentReviews' => Review::with(['tutorProfile.user', 'appointment.studentProfile.user'])->latest()->limit(5)->get(),
        ]);
    }
}
