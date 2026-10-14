<?php

namespace App\Domains\Reportreview\Services;

use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use Illuminate\Support\Str;

class ReportNotification
{
    // ใช้ร่วมกันตอนส่งรายงานและตอนแอดมินเปลี่ยนสถานะ
    public static function send(string $userId, string $message): void
    {
        // เพิ่มเพียงข้อมูลในตารางเดิม ถ้ามีประเภทนี้แล้วจะไม่สร้างซ้ำ
        NotificationType::firstOrCreate(
            ['notification_type_id' => 'REPORT0001'],
            ['type_name' => 'รายงาน']
        );

        Notification::create([
            'notification_id' => strtoupper(Str::random(10)),
            'message' => $message,
            'is_read' => false,
            'Users_user_id' => $userId,
            'NotificationType_notification_type_id' => 'REPORT0001',
        ]);
    }
}
