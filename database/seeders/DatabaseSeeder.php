<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // สร้างเฉพาะเมื่อยังไม่มีอีเมลนี้ จึงรัน db:seed ซ้ำได้
        // withTrashed ตรวจบัญชีที่ soft delete แล้วด้วย เพราะอีเมลยังต้องไม่ซ้ำ
        // ข้อมูลจาก factory ใช้เฉพาะตอนสร้างใหม่ ไม่เขียนทับชื่อหรือรหัสผ่านเดิม
        User::withTrashed()->firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->raw([
                'first_name' => 'Test',
                'last_name' => 'User',
            ])
        );

        // 🟢 เรียกใช้ SubjectSeeder ที่แยกไว้
        $this->call([
            SubjectSeeder::class,
            ReportReasonSeeder::class,
            AccountSeeder::class,
            TutorProfilePhotoSeeder::class,
            TutorSeeder::class,
            StudentSeeder::class,
            ReportSeeder::class,
            CompletedLessonSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
