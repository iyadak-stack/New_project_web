<?php

namespace App\Domains\Reportreview\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['appointment.studentProfile.user', 'appointment.studentUser.studentProfile', 'tutorProfile.user'])->latest()->get();
        foreach ($reviews as $review) {
            $review->appointment?->loadStudentProfile();
        }

        return view('domains.reportreview.reviews.index', compact('reviews'));
    }

    public function create()
    {
        // แบบฟอร์มเก่ามีรหัสนัดหมายตัวอย่าง ให้ใช้ฟอร์มจริงในหน้านัดหมายแทน
        return redirect()->route('appointments.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'Comment' => ['required', 'string', 'max:2000'],
            'Appointment_Appointment_id' => ['required', 'string', 'exists:appointments,Appointment_id'],
            'Tutor_profiles_tutor_id' => ['nullable', 'string'],
        ]);
        // ล็อกนัดหมายเพื่อกันส่งรีวิวพร้อมกันสองครั้ง
        DB::transaction(function () use ($request, $data) {
            $appointment = Appointment::lockForUpdate()->findOrFail($data['Appointment_Appointment_id']);
            $isOwner = (string) $appointment->Student_profiles_student_id === (string) $request->user()->user_id
                || (string) $appointment->studentProfile?->user_id === (string) $request->user()->user_id;
            abort_unless($isOwner, 403);
            if ($appointment->status !== 'completed') {
                throw ValidationException::withMessages(['appointment' => 'ต้องเรียนเสร็จก่อนส่งรีวิว']);
            }
            if (Review::withTrashed()->where('Appointment_Appointment_id', $appointment->Appointment_id)->exists()) {
                throw ValidationException::withMessages(['appointment' => 'นัดหมายนี้ส่งรีวิวแล้ว']);
            }
            if (! empty($data['Tutor_profiles_tutor_id']) && $data['Tutor_profiles_tutor_id'] !== $appointment->Tutor_profiles_tutor_id) {
                throw ValidationException::withMessages(['Tutor_profiles_tutor_id' => 'ติวเตอร์ไม่ตรงกับนัดหมาย']);
            }
            $review = Review::create([
                'Review_id' => strtoupper(Str::random(10)),
                'rating' => $data['rating'],
                'Comment' => $data['Comment'],
                'Appointment_Appointment_id' => $appointment->Appointment_id,
                // ใช้ติวเตอร์ของนัดหมาย ไม่เชื่อค่าที่ส่งมาจากฟอร์ม
                'Tutor_profiles_tutor_id' => $appointment->Tutor_profiles_tutor_id,
            ]);
            $this->updateAverageRating($review);
        });

        return redirect()->route('reviews.index')->with('success', 'บันทึกรีวิวเรียบร้อยแล้ว');
    }

    public function edit(Review $review)
    {
        Gate::authorize('update', $review);

        return view('domains.reportreview.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        Gate::authorize('update', $review);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'Comment' => ['required', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($review, $data) {
            $review->update($data);
            $this->updateAverageRating($review);
        });

        return redirect()->route('reviews.index')->with('success', 'แก้ไขรีวิวเรียบร้อยแล้ว');
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);
        DB::transaction(function () use ($review) {
            $review->delete();
            $this->updateAverageRating($review);
        });

        return redirect()->route('reviews.index')->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }

    private function updateAverageRating(Review $review): void
    {
        // คะแนนเฉลี่ยคิดเฉพาะรีวิวที่ไม่ได้ซ่อน และต้องเปลี่ยนพร้อมรีวิว
        TutorProfile::where('tutor_id', $review->Tutor_profiles_tutor_id)->lockForUpdate()->firstOrFail();
        $average = Review::where('Tutor_profiles_tutor_id', $review->Tutor_profiles_tutor_id)->avg('rating') ?? 0;
        TutorProfile::where('tutor_id', $review->Tutor_profiles_tutor_id)->update(['average_rating' => round($average, 2)]);
    }
}
