<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(StudentSeeder::class);
        $student = User::with('studentProfile')->where('user_id', 'USERSTU001')->first();
        if (! $student || ! $student->studentProfile) {
            return;
        }

        // รีวิวได้เฉพาะนัดหมายที่เรียนจบ ไม่เปลี่ยนสถานะนัดหมายเดิมเพื่อให้รีวิวได้
        $appointments = Appointment::where('status', 'completed')
            // ไม่สร้างข้อความรีวิวให้การเรียนจริงครั้งใหม่ของผู้ใช้ในอนาคต
            ->whereIn('Appointment_id', ['SEEDAPP001', 'SEEDAPP002', 'SEEDAPP003', 'SEEDAPP004'])
            ->where('end_datetime', '<', now())
            ->whereIn('Student_profiles_student_id', [$student->user_id, $student->studentProfile->id])
            ->whereIn('Tutor_profiles_tutor_id', ['TUTOR00001', 'TUTOR00002'])
            ->orderBy('start_datetime')
            ->get();

        $comments = [
            ['rating' => 5, 'comment' => 'อธิบายพื้นฐานได้ชัดเจน และให้ลองทำโจทย์ทีละขั้น'],
            ['rating' => 4, 'comment' => 'ช่วยทบทวนจุดที่ยังไม่เข้าใจและมีแบบฝึกหัดให้ฝึกต่อ'],
            ['rating' => 5, 'comment' => 'สอนเป็นกันเอง เข้าใจง่าย และตอบคำถามได้ครบ'],
            ['rating' => 5, 'comment' => 'เตรียมเนื้อหาตรงกับเป้าหมาย ได้ฝึกใช้ความรู้จริงระหว่างเรียน'],
        ];

        foreach ($appointments as $index => $appointment) {
            $data = $comments[$index % count($comments)];
            // บันทึกรีวิวกับคะแนนเฉลี่ยพร้อมกัน ป้องกันคะแนนไม่ตรงกับรายการ
            DB::transaction(function () use ($appointment, $data) {
                $tutorProfile = TutorProfile::whereKey($appointment->Tutor_profiles_tutor_id)
                    ->lockForUpdate()
                    ->first();
                if (! $tutorProfile) {
                    return;
                }

                // ตรวจรีวิวที่ถูกซ่อนด้วย จึงไม่สร้างซ้ำหรือคืนรีวิวที่แอดมินซ่อนไว้
                Review::withTrashed()->firstOrCreate([
                    'Appointment_Appointment_id' => $appointment->Appointment_id,
                ], [
                    'Review_id' => strtoupper(Str::random(10)),
                    'Tutor_profiles_tutor_id' => $tutorProfile->tutor_id,
                    'rating' => $data['rating'],
                    'Comment' => $data['comment'],
                ]);

                $average = Review::where('Tutor_profiles_tutor_id', $tutorProfile->tutor_id)->avg('rating');
                $tutorProfile->average_rating = round($average ?? 0, 2);
                $tutorProfile->save();
            });
        }

        if ($appointments->isEmpty()) {
            $this->command?->warn('ยังไม่มีนัดหมายที่เรียนจบของบัญชีพิม จึงยังไม่เพิ่มรีวิว');
        }
    }
}
