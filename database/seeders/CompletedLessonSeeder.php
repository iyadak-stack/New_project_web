<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompletedLessonSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([TutorSeeder::class, StudentSeeder::class]);

        $student = User::where('user_id', 'USERSTU001')->first();
        if (! $student || ! $student->studentProfile) {
            return;
        }

        // เพิ่มเฉพาะข้อมูลย้อนหลัง 4 รายการที่ได้รับอนุญาต ไม่เปลี่ยน availability
        $lessons = [
            ['id' => 'SEEDAPP001', 'tutor_id' => 'TUTOR00001', 'subject_id' => 'SUB-001', 'days_ago' => 12],
            ['id' => 'SEEDAPP002', 'tutor_id' => 'TUTOR00001', 'subject_id' => 'SUB-001', 'days_ago' => 10],
            ['id' => 'SEEDAPP003', 'tutor_id' => 'TUTOR00002', 'subject_id' => 'SUB-006', 'days_ago' => 8],
            ['id' => 'SEEDAPP004', 'tutor_id' => 'TUTOR00002', 'subject_id' => 'SUB-006', 'days_ago' => 6],
        ];

        // บันทึกเป็นชุด ถ้าฐานข้อมูลผิดพลาดให้ย้อนข้อมูลในชุดนี้
        DB::transaction(function () use ($student, $lessons) {
            foreach ($lessons as $lesson) {
                $tutorProfile = TutorProfile::approved()->find($lesson['tutor_id']);
                $subject = Subject::find($lesson['subject_id']);
                if (! $tutorProfile || ! $subject) {
                    continue;
                }

                $start = now()->subDays($lesson['days_ago'])->startOfDay()->addHours(10);
                $end = $start->copy()->addHour();

                // รหัสคงที่ทำให้รันซ้ำได้ ไม่แก้นัดหมายเดิมหรือคืนรายการที่ลบแล้ว
                $appointment = Appointment::withTrashed()->firstOrCreate([
                    'Appointment_id' => $lesson['id'],
                ], [
                    'Student_profiles_student_id' => $student->user_id,
                    'Tutor_profiles_tutor_id' => $tutorProfile->tutor_id,
                    'Subject_subject_id' => $subject->subject_id,
                    'start_datetime' => $start,
                    'end_datetime' => $end,
                    'status' => 'completed',
                    'lesson_notes' => 'ทบทวนพื้นฐานและทำแบบฝึกหัดตามวิชาที่นัดหมาย',
                ]);

                if ($appointment->wasRecentlyCreated) {
                    $appointment->created_at = $start->copy()->subDay();
                    $appointment->updated_at = $end;
                    $appointment->save();
                }
            }
        });
    }
}
