<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Scheduling\Models\Availability;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TutorDemoSeeder extends Seeder
{
    /**
     * Add sample tutors so the tutor cards and search pages have content to show.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call(SubjectSeeder::class);

        $tutors = [
            [
                'user_id' => 'USR-TUT001',
                'tutor_id' => 'TUT-000001',
                'first_name' => 'มินตรา',
                'last_name' => 'ใจดี',
                'email' => 'mintra.tutor@example.com',
                'bio' => 'ติวคณิตศาสตร์และแคลคูลัส เน้นปูพื้นฐานพร้อมฝึกโจทย์ทีละขั้น',
                'experience_years' => 5,
                'teaching_mode' => 'both',
                'average_rating' => 4.90,
                'subjects' => ['SUB-001', 'SUB-002'],
            ],
            [
                'user_id' => 'USR-TUT002',
                'tutor_id' => 'TUT-000002',
                'first_name' => 'ธนกฤต',
                'last_name' => 'แสงทอง',
                'email' => 'thanakrit.tutor@example.com',
                'bio' => 'สอนฟิสิกส์และคณิตศาสตร์ระดับมัธยม อธิบายแนวคิดให้เห็นภาพ',
                'experience_years' => 3,
                'teaching_mode' => 'online',
                'average_rating' => 4.70,
                'subjects' => ['SUB-001', 'SUB-003'],
            ],
            [
                'user_id' => 'USR-TUT003',
                'tutor_id' => 'TUT-000003',
                'first_name' => 'พิมพ์ชนก',
                'last_name' => 'วัฒนะ',
                'email' => 'pimchanok.tutor@example.com',
                'bio' => 'ติวภาษาอังกฤษเพื่อการเรียนและการสอบ ฝึกสนทนาและแกรมมาร์ให้ใช้ได้จริง',
                'experience_years' => 4,
                'teaching_mode' => 'both',
                'average_rating' => 4.80,
                'subjects' => ['SUB-006'],
            ],
        ];

        foreach ($tutors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'user_id' => $data['user_id'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => Hash::make('password'),
                    'role' => 'tutor',
                    'current_role' => 'tutor',
                    'is_active' => true,
                ]
            );

            $profile = TutorProfile::updateOrCreate(
                ['Users_user_id' => $user->user_id],
                [
                    'tutor_id' => $data['tutor_id'],
                    'bio' => $data['bio'],
                    'experience_years' => $data['experience_years'],
                    'teaching_mode' => $data['teaching_mode'],
                    'average_rating' => $data['average_rating'],
                ]
            );

            $profile->subjects()->syncWithoutDetaching($data['subjects']);

            foreach ([[2, 9, 12], [3, 13, 16], [5, 9, 12]] as [$daysAhead, $startHour, $endHour]) {
                $start = now()->addDays($daysAhead)->startOfDay()->setTime($startHour, 0);
                $end = $start->copy()->setTime($endHour, 0);

                Availability::firstOrCreate(
                    [
                        'user_id' => $user->user_id,
                        'start_datetime' => $start,
                        'end_datetime' => $end,
                    ],
                    ['availability_id' => Str::upper(Str::random(10))]
                );
            }
        }
    }
}
