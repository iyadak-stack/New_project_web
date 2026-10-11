<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'user_id' => 'ADM-000001',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'current_role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $reporter = User::firstOrCreate(
            ['email' => 'demo.student@example.com'],
            [
                'user_id' => 'USR-DEMO01',
                'first_name' => 'Demo',
                'last_name' => 'Student',
                'password' => Hash::make('password'),
                'role' => 'student',
                'current_role' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $tutor = TutorProfile::query()->first();
        $reason = ReportReason::query()->first();

        if ($tutor && $reason) {
            Report::firstOrCreate(
                ['Report_id' => 'RPT-DEMO01'],
                [
                    'description' => 'ข้อมูลตัวอย่างสำหรับทดสอบขั้นตอนตรวจสอบรายงานในแผง Admin',
                    'status' => 'pending',
                    'ReportReason_Reason_id' => $reason->Reason_id,
                    'Users_user_id' => $reporter->user_id,
                    'Reported_users_user_id' => $tutor->Users_user_id,
                ]
            );
        }
    }
}
