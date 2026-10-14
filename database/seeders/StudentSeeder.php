<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\StudentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // บัญชีเริ่มต้นสำหรับเชื่อมรายงานและรีวิว ไม่ทับรหัสผ่านเดิมเมื่อรันซ้ำ
        $user = User::withTrashed()->firstOrCreate(['email' => 'pim@example.com'], [
            'user_id' => 'USERSTU001',
            'first_name' => 'พิม',
            'last_name' => 'เรียนดี',
            'password' => Hash::make('password'),
            'role' => 'student',
            'current_role' => 'student',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ไม่คืนบัญชีที่ลบแล้ว และไม่ใช้บัญชีอื่นที่มีอีเมลตรงกัน
        if ($user->trashed() || $user->user_id !== 'USERSTU001') {
            return;
        }

        StudentProfile::firstOrCreate(['user_id' => $user->user_id], [
            'id' => 'STUDENT001',
            'bio' => 'สนใจเรียนคณิตศาสตร์และภาษาอังกฤษ เพื่อทบทวนพื้นฐานและเตรียมสอบ',
        ]);
    }
}
