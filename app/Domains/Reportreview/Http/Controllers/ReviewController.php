<?php

namespace App\Domains\Reportreview\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with([
            'appointment.studentProfile.user',
            'tutorProfile.user',
        ])->latest()->paginate(15);

        // ส่งข้อมูลไปยัง View หน้าแสดงรายการรีวิว
        return view('domains.reportreview.reviews.index', compact('reviews'));
    }

    public function create()
    {
        Gate::authorize('create', Review::class);
        $studentProfile = auth()->user()->studentProfile;
        $appointments = $studentProfile
            ? Appointment::with('tutorProfile.user')
                ->where('Student_profiles_student_id', $studentProfile->id)
                ->whereDoesntHave('review')
                ->orderByDesc('start_datetime')
                ->get()
            : collect();

        return view('domains.reportreview.reviews.create', compact('appointments'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Review::class);
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'Comment' => ['required', 'string'],
            'Appointment_Appointment_id' => ['required', 'string', 'size:10', 'exists:appointments,Appointment_id'],
        ]);

        $appointment = Appointment::with('studentProfile')
            ->where('Appointment_id', $validated['Appointment_Appointment_id'])
            ->firstOrFail();

        abort_unless($appointment->studentProfile?->user_id === auth()->user()->user_id, 403);
        if (Review::where('Appointment_Appointment_id', $appointment->Appointment_id)->exists()) {
            return back()->withErrors(['Appointment_Appointment_id' => 'นัดหมายนี้มีรีวิวแล้ว']);
        }

        Review::create([
            'Review_id' => strtoupper(Str::random(10)),
            'rating' => $validated['rating'],
            'Comment' => $validated['Comment'],
            'Appointment_Appointment_id' => $validated['Appointment_Appointment_id'],
            'Tutor_profiles_tutor_id' => $appointment->Tutor_profiles_tutor_id,
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'บันทึกรีวิวเรียบร้อยแล้ว');
    }

    public function edit(Review $review)
    {
        // ใช้ Gate เพื่อความปลอดภัยในการตรวจสอบสิทธิ์
        Gate::authorize('update', $review);

        return view('domains.reportreview.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        Gate::authorize('update', $review);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'Comment' => ['required', 'string'],
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'Comment' => $validated['Comment'],
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'แก้ไขรีวิวเรียบร้อยแล้ว');
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }
}
