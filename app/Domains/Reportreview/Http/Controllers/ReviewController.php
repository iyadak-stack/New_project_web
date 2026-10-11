<?php

namespace App\Domains\Reportreview\Http\Controllers;

use App\Domains\Booking\Http\Controllers\AppointmentController;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index()
    {
        if (auth()->user()->current_role === 'tutor') {
            return redirect()->to(route('tutor.profile').'#reviews');
        }

        return redirect()->route('appointments.index');
    }

    public function create()
    {
        return redirect()->route('appointments.index')->with('success', 'เลือกนัดหมายที่เรียนเสร็จแล้วเพื่อเขียนรีวิว');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['Appointment_Appointment_id' => ['required', 'exists:appointments,Appointment_id']]);
        $appointment = Appointment::findOrFail($data['Appointment_Appointment_id']);

        // ใช้กฎบันทึกรีวิวชุดเดียวกับหน้านัดหมาย เพื่อไม่ให้ข้ามขั้นตอนยืนยันเรียนจบ
        return app(AppointmentController::class)->storeReview($request, $appointment);
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
