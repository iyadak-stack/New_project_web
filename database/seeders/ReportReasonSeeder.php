<?php

namespace Database\Seeders;

use App\Domains\Reportreview\Models\ReportReason;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'ข้อมูลโปรไฟล์ไม่เหมาะสม',
            'พฤติกรรมไม่เหมาะสม',
            'ข้อมูลการสอนทำให้เข้าใจผิด',
            'ปัญหาการนัดหมายหรือการชำระเงิน',
            'อื่น ๆ',
        ] as $index => $reason) {
            ReportReason::firstOrCreate(
                ['Reason_id' => 'RSN-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT)],
                ['reason_name' => $reason]
            );
        }
    }
}
