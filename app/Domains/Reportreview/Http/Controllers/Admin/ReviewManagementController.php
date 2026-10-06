<?php

namespace App\Domains\Reportreview\Http\Controllers\Admin;

use App\Domains\Reportreview\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class ReviewManagementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        Gate::authorize('viewAny', Review::class);

        $reviews = Review::with(['tutorProfile.user', 'appointment.studentProfile.user', 'reports'])
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('Review_id', 'like', "%{$search}%")
                        ->orWhere('Comment', 'like', "%{$search}%")
                        ->orWhereHas('appointment', fn ($appointment) => $appointment->where('Appointment_id', 'like', "%{$search}%"))
                        ->orWhereHas('tutorProfile.user', fn ($user) => $user->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('appointment.studentProfile.user', fn ($user) => $user->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('domains.reportreview.admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        Gate::authorize('view', $review);
        $review->load(['appointment.studentProfile.user', 'tutorProfile.user', 'reports']);

        return view('domains.reportreview.admin.reviews.show', compact('review'));
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);
        $review->delete();

        return back()->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }
}
