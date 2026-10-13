<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Services\ReportNotification;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([StudentSeeder::class, ReportReasonSeeder::class]);

        $student = User::where('user_id', 'USERSTU001')->first();
        if (! $student || ! $student->is_active) {
            return;
        }

        $reports = [
            [
                'id' => 'SEEDRPT001',
                'tutor_id' => 'TUTOR00001',
                'reason_id' => 'RPTTYPE001',
                'description' => 'ข้อมูลวิชาที่สอนในโปรไฟล์กับข้อมูลที่แจ้งก่อนเรียนไม่ตรงกัน ขอให้ตรวจสอบรายละเอียด',
                'status' => 'pending',
                'status_name' => 'รอแอดมินตรวจสอบ',
            ],
            [
                'id' => 'SEEDRPT002',
                'tutor_id' => 'TUTOR00002',
                'reason_id' => 'RPTTYPE002',
                'description' => 'พบการใช้ถ้อยคำไม่เหมาะสมในการติดต่อสอบถาม ขอให้แอดมินช่วยตรวจสอบ',
                'status' => 'in_progress',
                'status_name' => 'กำลังตรวจสอบ',
            ],
            [
                'id' => 'SEEDRPT003',
                'tutor_id' => 'TUTOR00001',
                'reason_id' => 'RPTTYPE004',
                'description' => 'ข้อมูลช่องทางติดต่อในโปรไฟล์ไม่ชัดเจน ได้รับการตรวจสอบและแก้ไขข้อมูลแล้ว',
                'status' => 'resolved',
                'status_name' => 'ตรวจสอบเสร็จแล้ว',
            ],
        ];

        $admins = User::where('is_active', true)
            ->where(function ($query) {
                $query->where('role', 'admin')->orWhere('current_role', 'admin');
            })
            ->get();

        foreach ($reports as $data) {
            $tutorProfile = TutorProfile::approved()->find($data['tutor_id']);
            if (! $tutorProfile || $tutorProfile->Users_user_id === $student->user_id) {
                continue;
            }

            // รายงานกับแจ้งเตือนต้องสำเร็จพร้อมกัน เพื่อให้แอดมินเห็นรายการจริง
            DB::transaction(function () use ($student, $admins, $data) {
                $report = Report::firstOrCreate(['Report_id' => $data['id']], [
                    'Users_user_id' => $student->user_id,
                    'Tutor_profiles_tutor_id' => $data['tutor_id'],
                    'ReportReason_Reason_id' => $data['reason_id'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                ]);

                // รันซ้ำไม่ทับผลตรวจของแอดมิน และไม่เพิ่มแจ้งเตือนซ้ำ
                if (! $report->wasRecentlyCreated) {
                    return;
                }

                ReportNotification::send($student->user_id, 'รายงานรหัส '.$report->Report_id.' '.$data['status_name']);
                foreach ($admins as $admin) {
                    ReportNotification::send($admin->user_id, 'รายงานรหัส '.$report->Report_id.' สถานะ'.$data['status_name']);
                }
            });
        }
    }
}
