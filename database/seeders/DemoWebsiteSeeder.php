<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\Reportreview\Models\Review;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        // บัญชีรหัสผ่านตัวอย่างใช้เฉพาะเครื่องพัฒนา
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call(SubjectSeeder::class);

        // ใช้รหัสเดิมทุกครั้ง จึงรันซ้ำได้โดยไม่ล้างข้อมูลที่เคยทดลองไว้
        DB::transaction(function () {
            $accounts = [
                ['id' => 'DEMOUSR001', 'email' => 'demo.admin@example.com', 'name' => 'แอดมินตัวอย่าง', 'role' => 'admin'],
                ['id' => 'DEMOUSR002', 'email' => 'demo.learner@example.com', 'name' => 'นักเรียนตัวอย่าง', 'role' => 'student'],
                ['id' => 'DEMOUSR003', 'email' => 'demo.tutor@example.com', 'name' => 'ครูคณิตตัวอย่าง', 'role' => 'tutor'],
                ['id' => 'DEMOUSR004', 'email' => 'demo.english@example.com', 'name' => 'ครูอังกฤษตัวอย่าง', 'role' => 'tutor'],
                ['id' => 'DEMOUSR005', 'email' => 'demo.pending@example.com', 'name' => 'ติวเตอร์รอตรวจ', 'role' => 'tutor'],
                ['id' => 'DEMOUSR006', 'email' => 'demo.rejected@example.com', 'name' => 'ติวเตอร์แก้ใบสมัคร', 'role' => 'tutor'],
                ['id' => 'DEMOUSR007', 'email' => 'demo.disabled@example.com', 'name' => 'นักเรียนถูกปิดบัญชี', 'role' => 'student'],
            ];
            $users = [];
            foreach ($accounts as $index => $account) {
                $user = User::firstOrCreate(['email' => $account['email']], [
                    'user_id' => $account['id'],
                    'first_name' => $account['name'],
                    'last_name' => 'ทดลอง',
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                    'current_role' => $account['role'],
                    'is_active' => $account['email'] !== 'demo.disabled@example.com',
                ]);
                if ($user->wasRecentlyCreated && ! $user->is_active) {
                    $user->disabled_reason = 'ข้อมูลทดลอง: บัญชีถูกปิดเนื่องจากส่งคำขอจองรบกวนผู้อื่น';
                    $user->save();
                }
                $users[$account['email']] = $user;
                StudentProfile::firstOrCreate(['user_id' => $user->user_id], [
                    'id' => 'DEMOSTU00'.($index + 1),
                    'bio' => 'ข้อมูลตัวอย่างสำหรับลองใช้งานเว็บ',
                ]);
                Contact::firstOrCreate(['Users_user_id' => $user->user_id], [
                    'contact_id' => 'DEMOCON00'.($index + 1),
                    'line_id' => 'demo-user-'.($index + 1),
                    'discord_id' => 'demo-user-'.($index + 1),
                    'zoom_link' => 'https://example.com/demo/zoom',
                    'google_meet_link' => 'https://example.com/demo/meet',
                    'meeting_location' => 'ห้องสมุด ชั้น 2 (สถานที่ตัวอย่าง)',
                ]);
            }

            $tutorAccounts = [
                ['email' => 'demo.tutor@example.com', 'status' => 'approved', 'subject' => 'SUB-001'],
                ['email' => 'demo.english@example.com', 'status' => 'approved', 'subject' => 'SUB-006'],
                ['email' => 'demo.pending@example.com', 'status' => 'pending', 'subject' => 'SUB-003'],
                ['email' => 'demo.rejected@example.com', 'status' => 'rejected', 'subject' => 'SUB-007'],
            ];
            $tutors = [];
            foreach ($tutorAccounts as $index => $account) {
                $tutorUser = $users[$account['email']];
                $profile = TutorProfile::firstOrCreate(['Users_user_id' => $tutorUser->user_id], [
                    'tutor_id' => 'DEMOTUT00'.($index + 1),
                    'bio' => 'โปรไฟล์ตัวอย่าง สอนพื้นฐาน อธิบายทีละขั้น และฝึกทำโจทย์ร่วมกัน',
                    'experience_years' => 2,
                    'teaching_mode' => 'both',
                    'average_rating' => 0,
                    'approval_status' => $account['status'],
                ]);
                if ($profile->wasRecentlyCreated) {
                    $profile->submitted_at = now();
                    if ($account['status'] !== 'pending') {
                        $profile->reviewed_at = now();
                        $profile->reviewed_by = $users['demo.admin@example.com']->user_id;
                    }
                    if ($account['status'] === 'rejected') {
                        $profile->reject_reason = 'กรุณาเพิ่มรายละเอียดประสบการณ์สอน (ตัวอย่าง)';
                    }
                    $profile->save();
                }
                $profile->subjects()->syncWithoutDetaching([$account['subject']]);
                $tutors[$account['email']] = $profile;

                // สร้างวันว่างล่วงหน้าให้ติวเตอร์ที่อนุมัติแล้ว เพื่อทดลองจองจริง
                if ($profile->approval_status === 'approved') {
                    for ($day = 1; $day <= 7; $day++) {
                        Availability::firstOrCreate(['availability_id' => 'DEMOAV'.$index.'00'.$day], [
                            'user_id' => $tutorUser->user_id,
                            'start_datetime' => now()->addDays($day)->setTime(9, 0),
                            'end_datetime' => now()->addDays($day)->setTime(10, 0),
                        ]);
                    }
                }
            }

            $student = $users['demo.learner@example.com'];
            $tutor = $tutors['demo.tutor@example.com'];
            $bookingExamples = [
                ['id' => 'DEMOAPP001', 'status' => 'pending', 'day' => 1],
                ['id' => 'DEMOAPP002', 'status' => 'confirmed', 'day' => 2],
                ['id' => 'DEMOAPP003', 'status' => 'completed', 'day' => -2],
                ['id' => 'DEMOAPP004', 'status' => 'completed', 'day' => -3],
                ['id' => 'DEMOAPP005', 'status' => 'cancelled', 'day' => 3],
                ['id' => 'DEMOAPP006', 'status' => 'confirmed', 'day' => -1],
            ];
            foreach ($bookingExamples as $booking) {
                Appointment::firstOrCreate(['Appointment_id' => $booking['id']], [
                    'status' => $booking['status'],
                    'start_datetime' => now()->addDays($booking['day'])->setTime(9, 0),
                    'end_datetime' => now()->addDays($booking['day'])->setTime(10, 0),
                    'Subject_subject_id' => 'SUB-001',
                    'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                    'Student_profiles_student_id' => $student->user_id,
                    'lesson_notes' => 'เตรียมสมุดและโจทย์ที่ต้องการถาม ลิงก์ห้องเรียนในข้อมูลนี้เป็นตัวอย่าง',
                    'rejection_reason' => $booking['status'] === 'cancelled' ? 'ติวเตอร์ไม่สะดวกในเวลานี้ (ตัวอย่าง)' : null,
                ]);
            }
            $review = Review::firstOrCreate(['Review_id' => 'DEMOREV001'], [
                'Appointment_Appointment_id' => 'DEMOAPP003',
                'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                'rating' => 5,
                'Comment' => 'สอนเข้าใจง่าย อธิบายละเอียด และได้ลองทำโจทย์ด้วยตัวเอง (รีวิวตัวอย่าง)',
            ]);
            if ($review->wasRecentlyCreated) {
                $tutor->update(['average_rating' => $tutor->reviews()->avg('rating')]);
            }
            Favorite::firstOrCreate(['favorite_id' => 'DEMOFAV001'], [
                'Users_user_id' => $student->user_id,
                'Tutor_profiles_tutor_id' => $tutor->tutor_id,
                'Subject_subject_id' => null,
            ]);
            NotificationType::firstOrCreate(['notification_type_id' => 'DEMOTYPE01'], ['type_name' => 'ข้อมูลทดลอง']);
            foreach ([$student, $users['demo.tutor@example.com'], $users['demo.admin@example.com']] as $index => $recipient) {
                Notification::firstOrCreate(['notification_id' => 'DEMONOT00'.($index + 1)], [
                    'message' => 'แจ้งเตือนตัวอย่าง: มีข้อมูลใหม่ให้ตรวจสอบในหน้านัดหมายหรือใบสมัครติวเตอร์',
                    'is_read' => false,
                    'Users_user_id' => $recipient->user_id,
                    'NotificationType_notification_type_id' => 'DEMOTYPE01',
                ]);
            }
            ReportReason::firstOrCreate(['Reason_id' => 'DEMOREAS01'], ['reason_name' => 'ข้อมูลโปรไฟล์ไม่ตรงกับความจริง']);
            Report::firstOrCreate(['Report_id' => 'DEMORPT001'], [
                'description' => 'รายงานตัวอย่างสำหรับฝึกตรวจสอบ ไม่ใช่ข้อร้องเรียนจริง',
                'status' => 'pending',
                'ReportReason_Reason_id' => 'DEMOREAS01',
                'Users_user_id' => $student->user_id,
                'Reported_users_user_id' => $tutor->Users_user_id,
            ]);
            Storage::disk('local')->put('demo/report-example.txt', 'Example evidence for the demo report.');
            DB::table('report_evidences')->updateOrInsert([
                'Report_Report_id' => 'DEMORPT001',
                'FilePath' => 'demo/report-example.txt',
            ], ['file_type' => 'text/plain']);
        });
    }
}
