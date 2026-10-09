<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Booking\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // รายการวิชาที่ระบบฟิกไว้ให้เลือก
        $fixedSubjects = [
            ['subject_id' => 'SUB-001', 'subject_name' => 'คณิตศาสตร์ทั่วไป'],
            ['subject_id' => 'SUB-002', 'subject_name' => 'แคลคูลัส 1'],
            ['subject_id' => 'SUB-003', 'subject_name' => 'ฟิสิกส์'],
            ['subject_id' => 'SUB-004', 'subject_name' => 'เคมี'],
            ['subject_id' => 'SUB-005', 'subject_name' => 'ชีววิทยา'],
            ['subject_id' => 'SUB-006', 'subject_name' => 'ภาษาอังกฤษ'],
            ['subject_id' => 'SUB-007', 'subject_name' => 'การเขียนโปรแกรม / Coding'],
        ];

        foreach ($fixedSubjects as $subject) {
            Subject::firstOrCreate(
                ['subject_id' => $subject['subject_id']],
                ['subject_name' => $subject['subject_name']]
            );
        }
    }
}