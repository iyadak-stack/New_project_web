<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ข้อมูลพื้นฐานและตัวอย่างที่เชื่อมถึงกัน รันซ้ำได้โดยไม่ล้างฐานข้อมูล
        $this->call([
            SubjectSeeder::class,
            ReportReasonSeeder::class,
            DemoWebsiteSeeder::class,
        ]);
    }
}
