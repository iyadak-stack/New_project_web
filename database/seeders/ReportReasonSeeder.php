<?php

namespace Database\Seeders;

use App\Domains\Reportreview\Models\ReportReason;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'RPTTYPE001' => 'ข้อมูลโปรไฟล์หรือแอบอ้างข้อมูลเท็จ',
            'RPTTYPE002' => 'พฤติกรรมหรือภาษาไม่เหมาะสม',
            'RPTTYPE003' => 'เข้าข่ายหลอกลวง / ฉ้อโกง',
            'RPTTYPE004' => 'อื่น ๆ',
        ];

        foreach ($reasons as $id => $name) {
            // เรียกซ้ำได้ และไม่เขียนทับชื่อประเภทที่แอดมินแก้ไว้แล้ว
            ReportReason::firstOrCreate(['Reason_id' => $id], ['reason_name' => $name]);
        }
    }
}
