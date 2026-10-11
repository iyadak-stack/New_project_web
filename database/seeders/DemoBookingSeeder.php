<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoBookingSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $student = User::where('email', 'demo.student@example.com')->first();
        $tutor = TutorProfile::where('tutor_id', 'TUT-000001')->first();
        if (! $student || ! $tutor) {
            return;
        }

        StudentProfile::firstOrCreate(
            ['user_id' => $student->user_id],
            ['id' => 'STU-DEMO01', 'bio' => 'บัญชีนักเรียนทดลองสำหรับทดสอบการจอง']
        );

        $subjectId = $tutor->subjects()->value('subjects.subject_id');
        if ($subjectId) {
            Appointment::firstOrCreate(
                ['Appointment_id' => 'APPDEMO001'],
                [
                    'status' => 'confirmed',
                    'start_datetime' => now()->subDays(2)->setTime(10, 0),
                    'end_datetime' => now()->subDays(2)->setTime(11, 0),
                    'Subject_subject_id' => $subjectId,
                    'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                    'Student_profiles_student_id' => $student->user_id,
                ]
            );
        }

        $contact = Contact::firstOrCreate(
            ['Users_user_id' => $tutor->Users_user_id],
            [
                'contact_id' => Str::upper(Str::random(10)),
                'zoom_link' => 'https://zoom.us/j/1234567890',
                'line_id' => 'demo-tutor-line',
            ]
        );
        if (! $contact->zoom_link) {
            $contact->forceFill(['zoom_link' => 'https://zoom.us/j/1234567890'])->save();
        }
    }
}
