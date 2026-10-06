<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('report_reasons')->insertOrIgnore([
            ['Reason_id' => 'RE00000001', 'reason_name' => 'เนื้อหาไม่เหมาะสม'],
            ['Reason_id' => 'RE00000002', 'reason_name' => 'ข้อมูลไม่ถูกต้อง'],
            ['Reason_id' => 'RE00000003', 'reason_name' => 'การคุกคามหรือไม่สุภาพ'],
            ['Reason_id' => 'RE00000004', 'reason_name' => 'สแปมหรือโฆษณา'],
            ['Reason_id' => 'RE00000005', 'reason_name' => 'อื่น ๆ'],
        ]);
    }

    public function down(): void
    {
        DB::table('report_reasons')->whereIn('Reason_id', [
            'RE00000001', 'RE00000002', 'RE00000003', 'RE00000004', 'RE00000005',
        ])->delete();
    }
};
