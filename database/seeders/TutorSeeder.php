<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        // ใช้รายวิชากลางเดิม จึงเรียก seeder นี้แยกจาก DatabaseSeeder ได้
        $this->call(SubjectSeeder::class);

        $tutors = [
            [
                'number' => '001',
                'first_name' => 'เนเน่',
                'last_name' => 'ศิริกุล',
                'email' => 'nene@example.com',
                'photo' => 'เนเน่.jpg',
                'subject_id' => 'SUB-001',
                'bio' => 'สอนคณิตศาสตร์ตั้งแต่พื้นฐาน อธิบายทีละขั้นและฝึกโจทย์ไปด้วยกัน',
                'years' => 2,
                'mode' => 'online',
                'status' => 'approved',
            ],
            [
                'number' => '002',
                'first_name' => 'แพรว',
                'last_name' => 'วัฒนกุล',
                'email' => 'praew@example.com',
                'photo' => 'แพรว.jpg',
                'subject_id' => 'SUB-006',
                'bio' => 'ฝึกภาษาอังกฤษทั้งไวยากรณ์และบทสนทนา ปรับเนื้อหาตามพื้นฐานผู้เรียน',
                'years' => 3,
                'mode' => 'both',
                'status' => 'approved',
            ],
            [
                'number' => '003',
                'first_name' => 'กาย',
                'last_name' => 'ธนากร',
                'email' => 'kai@example.com',
                'photo' => 'กาย.jpg',
                'subject_id' => 'SUB-007',
                'bio' => 'สอนเขียนโปรแกรมเบื้องต้นและฝึกแก้โจทย์ด้วยตัวอย่างที่เข้าใจง่าย',
                'years' => 1,
                'mode' => 'online',
                'status' => 'rejected',
            ],
            [
                'number' => '004',
                'first_name' => 'ต้น',
                'last_name' => 'ปัญญาวัฒน์',
                'email' => 'ton@example.com',
                'photo' => 'ต้น.jpg',
                'subject_id' => 'SUB-003',
                'bio' => 'ทบทวนฟิสิกส์และฝึกวิเคราะห์โจทย์ พร้อมอธิบายหลักการก่อนใช้สูตร',
                'years' => 1,
                'mode' => 'onsite',
                'status' => 'rejected',
            ],
            [
                'number' => '005',
                'first_name' => 'นนท์',
                'last_name' => 'เจริญชัย',
                'email' => 'non@example.com',
                'photo' => 'นนท์.jpg',
                'subject_id' => 'SUB-002',
                'bio' => 'สอนแคลคูลัสเรื่องลิมิต อนุพันธ์ และอินทิกรัล พร้อมโจทย์ฝึกทบทวน',
                'years' => 2,
                'mode' => 'online',
                'status' => 'pending',
            ],
            [
                'number' => '006',
                'first_name' => 'เฟิร์น',
                'last_name' => 'สุขสวัสดิ์',
                'email' => 'fern@example.com',
                'photo' => 'เฟิร์น.jpg',
                'subject_id' => 'SUB-005',
                'bio' => 'สรุปชีววิทยาเป็นหัวข้อและเชื่อมเนื้อหาเข้าด้วยกัน เพื่อให้จำและเข้าใจง่าย',
                'years' => 1,
                'mode' => 'both',
                'status' => 'pending',
            ],
        ];

        foreach ($tutors as $data) {
            $photo = 'tutor-profiles/'.$data['photo'];
            // รูปต้นฉบับอยู่ในโปรเจกต์ ไม่ต้องพึ่งโฟลเดอร์บนเครื่องของผู้พัฒนา
            if (! Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->put(
                    $photo,
                    file_get_contents(public_path('images/'.$photo))
                );
            }

            // บันทึกบัญชี โปรไฟล์ และวิชาพร้อมกัน ถ้าผิดพลาดให้ย้อนชุดนี้
            DB::transaction(function () use ($data, $photo) {
                $userId = 'USERTUT'.$data['number'];
                $currentRole = 'student';
                if ($data['status'] === 'approved') {
                    $currentRole = 'tutor';
                }

                // ตรวจบัญชีที่ลบแบบ soft delete ด้วย เพื่อไม่ให้อีเมลซ้ำ
                $user = User::withTrashed()->firstOrCreate([
                    'email' => $data['email'],
                ], [
                    'user_id' => $userId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => Hash::make('password'),
                    'role' => 'tutor',
                    'current_role' => $currentRole,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'profile_picture' => $photo,
                ]);

                // ไม่เปิดบัญชีที่ลบแล้วหรือใช้บัญชีอื่นที่มีอีเมลตรงกัน
                if ($user->trashed() || $user->user_id !== $userId) {
                    return;
                }

                StudentProfile::firstOrCreate(['user_id' => $userId], [
                    'id' => 'STUTUT0'.$data['number'],
                ]);

                $rejectReason = null;
                if ($data['status'] === 'rejected') {
                    $rejectReason = 'กรุณาเพิ่มเติมรายละเอียดการสอนและประสบการณ์ก่อนส่งคำขอใหม่';
                }

                // firstOrCreate ไม่ทับสถานะที่แอดมินตัดสินใจภายหลัง
                $tutorProfile = TutorProfile::firstOrCreate(['Users_user_id' => $userId], [
                    'tutor_id' => 'TUTOR00'.$data['number'],
                    'bio' => $data['bio'],
                    'experience_years' => $data['years'],
                    'teaching_mode' => $data['mode'],
                    'approval_status' => $data['status'],
                    'submitted_at' => now(),
                    'reject_reason' => $rejectReason,
                ]);

                $subject = Subject::findOrFail($data['subject_id']);
                $tutorProfile->subjects()->syncWithoutDetaching([$subject->subject_id]);

                Contact::firstOrCreate(['Users_user_id' => $userId], [
                    'contact_id' => 'CONTUT0'.$data['number'],
                    'line_id' => strstr($data['email'], '@', true),
                ]);
            });
        }
    }
}
