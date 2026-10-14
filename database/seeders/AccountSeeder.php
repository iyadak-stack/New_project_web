<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\StudentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // บัญชีตัวอย่างเพิ่มได้โดยไม่เปลี่ยนรหัสผ่านหรือข้อมูลบัญชีที่มีอยู่
        $accounts = [
            ['id' => 'DEMOSTU002', 'email' => 'student2.demo@example.com', 'name' => 'พลอย', 'role' => 'student'],
            ['id' => 'DEMOSTU003', 'email' => 'student3.demo@example.com', 'name' => 'ต้น', 'role' => 'student'],
            ['id' => 'DEMOADM001', 'email' => 'admin.demo@example.com', 'name' => 'แอดมิน', 'role' => 'admin'],
        ];
        foreach ($accounts as $account) {
            // ถ้าบัญชีถูก soft delete ไม่สร้างทับหรือเปิดใช้งานเอง
            User::withTrashed()->firstOrCreate(['email' => $account['email']], [
                'user_id' => $account['id'],
                'first_name' => $account['name'],
                'last_name' => 'ตัวอย่าง',
                'role' => $account['role'],
                'current_role' => $account['role'],
                'password' => Hash::make('Demo12345!'),
                'is_active' => true,
            ]);
        }

        // สร้างนักเรียนสาธิตและติวเตอร์ทั้ง 3 คน
        $this->call(FavoritePageSeeder::class);

        // สร้างโปรไฟล์นักเรียนให้แต่ละคน โดยไม่สร้าง Profile ซ้ำ
        foreach (User::where('role', '!=', 'admin')->get() as $user) {
            StudentProfile::firstOrCreate(['user_id' => $user->user_id], [
                'id' => strtoupper(Str::random(10)),
                'bio' => 'โปรไฟล์นักเรียน',
            ]);
        }
    }
}
