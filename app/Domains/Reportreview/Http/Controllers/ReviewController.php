<?php

namespace App\Domains\Reportreview\Http\Controllers;

use App\Http\Controllers\Controller;
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
            'tutorProfile'
        ])->get();

        // ส่งข้อมูลไปยัง View หน้าแสดงรายการรีวิว
        return view('reviews.index', compact('reviews'));
    }

    public function create()
    {
        return view('reviews.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'Comment' => ['required', 'string'],
            'Appointment_Appointment_id' => ['required', 'string', 'size:10'],
            'Tutor_profiles_tutor_id' => ['required', 'string'],
        ]);

        Review::create([
            'Review_id' => strtoupper(Str::random(10)),
            'rating' => $validated['rating'],
            'Comment' => $validated['Comment'],
            'Appointment_Appointment_id' => $validated['Appointment_Appointment_id'],
            'Tutor_profiles_tutor_id' => $validated['Tutor_profiles_tutor_id'],
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'บันทึกรีวิวเรียบร้อยแล้ว');
    }

    public function edit(Review $review)
    {
        // ใช้ Gate เพื่อความปลอดภัยในการตรวจสอบสิทธิ์
        Gate::authorize('update', $review);

        return view('reviews.edit', compact('review'));
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