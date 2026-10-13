<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FavoritePageSeeder extends Seeder
{
    public function run(): void
    {
        // ใช้สำหรับสาธิตในเครื่องพัฒนาเท่านั้น ไม่เพิ่มข้อมูลตัวอย่างบน Production
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::transaction(function () {
            // firstOrCreate ทำให้เรียก Seeder ซ้ำได้ โดยไม่สร้างผู้ใช้ซ้ำ
            $student = User::firstOrCreate(['email' => 'favorites.demo@example.com'], [
                'user_id' => 'DEMOSTU001',
                'first_name' => 'นักเรียน',
                'last_name' => 'ตัวอย่าง',
                'password' => Hash::make('Demo12345!'),
                'role' => 'student',
                'current_role' => 'student',
                'is_active' => true,
            ]);

            // ข้อมูลพื้นฐานของติวเตอร์แต่ละคน อ่านและเปลี่ยนค่าได้จากอาร์เรย์นี้
            $tutors = [
                ['name' => 'ณัฐ', 'surname' => 'ใจดี', 'subject' => 'คณิตศาสตร์', 'years' => 2, 'mode' => 'both', 'bio' => 'ค่อย ๆ ปูพื้นฐานจากเรื่องที่ยังไม่เข้าใจ แล้วฝึกโจทย์ไปด้วยกัน เหมาะกับคนที่อยากเริ่มต้นใหม่'],
                ['name' => 'แบงค์', 'surname' => 'สอนเข้าใจ', 'subject' => 'ฟิสิกส์', 'years' => 3, 'mode' => 'online', 'bio' => 'อธิบายหลักการด้วยตัวอย่างใกล้ตัว พร้อมฝึกคิดโจทย์ทีละขั้นและทบทวนก่อนสอบ'],
                ['name' => 'มายด์', 'surname' => 'ภาษาอังกฤษ', 'subject' => 'ภาษาอังกฤษ', 'years' => 1, 'mode' => 'onsite', 'bio' => 'ฝึกสนทนาและทบทวนไวยากรณ์ในบรรยากาศสบาย ๆ ปรับเนื้อหาให้เหมาะกับเป้าหมายของผู้เรียน'],
            ];

            $tutorIds = [];

            foreach ($tutors as $index => $data) {
                $number = $index + 1;
                $user = User::firstOrCreate(['email' => "tutor{$number}.demo@example.com"], [
                    'user_id' => 'DEMOUSE00'.$number,
                    'first_name' => $data['name'],
                    'last_name' => $data['surname'],
                    'password' => Hash::make('Demo12345!'),
                    'role' => 'tutor',
                    'current_role' => 'tutor',
                    'is_active' => true,
                ]);

                $tutor = TutorProfile::firstOrCreate(['Users_user_id' => $user->user_id], [
                    'tutor_id' => 'DEMOTUT00'.$number,
                    'bio' => $data['bio'],
                    'experience_years' => $data['years'],
                    'teaching_mode' => $data['mode'],
                ]);

                $subject = Subject::firstOrCreate(['subject_name' => $data['subject']], [
                    'subject_id' => 'DEMOSUB00'.$number,
                ]);

                // เชื่อมวิชาผ่านตารางกลาง โดยไม่ลบวิชาเดิมที่ติวเตอร์มีอยู่
                $tutor->subjects()->syncWithoutDetaching([$subject->subject_id]);
                $tutorIds[] = $tutor->tutor_id;
            }

            // เพิ่มให้บัญชีสาธิตและนักเรียนเดิมที่ยังไม่มีรายการโปรดเท่านั้น
            // บัญชีที่มีรายการโปรดอยู่แล้วจะไม่ถูกเปลี่ยนแปลง
            // บัญชีที่เพิ่งสร้างอาจยังไม่มี studentProfile ให้ตรวจบทบาทด้วย
            $students = User::where(function ($query) {
                $query->whereHas('studentProfile')
                    ->orWhere('role', 'student')
                    ->orWhere('current_role', 'student');
            })->get();
            if (! $students->contains('user_id', $student->user_id)) {
                $students->push($student);
            }

            foreach ($students as $viewer) {
                if (Favorite::withTrashed()->where('Users_user_id', $viewer->user_id)->whereNotNull('Tutor_profiles_tutor_id')->exists()) {
                    continue;
                }

                foreach ($tutorIds as $index => $tutorId) {
                    $favorite = Favorite::create([
                        'favorite_id' => strtoupper(Str::random(10)),
                        'Users_user_id' => $viewer->user_id,
                        'Tutor_profiles_tutor_id' => $tutorId,
                    ]);
                    // created_at ไม่อยู่ใน fillable จึงกำหนดแยกหลังสร้างรายการ
                    $favorite->created_at = now()->subMinutes($index);
                    $favorite->save();
                }
            }
        });
    }
}
