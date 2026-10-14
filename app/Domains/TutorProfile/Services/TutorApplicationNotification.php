<?php

namespace App\Domains\TutorProfile\Services;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use Illuminate\Support\Str;

class TutorApplicationNotification
{
    public static function send(string $userId, string $message): void
    {
        NotificationType::firstOrCreate(
            ['notification_type_id' => 'TUTORAPP01'], ['type_name' => 'คำขอเป็นติวเตอร์']
        );
        Notification::create([
            'notification_id' => strtoupper(Str::random(10)), 'message' => $message,
            'is_read' => false, 'Users_user_id' => $userId,
            'NotificationType_notification_type_id' => 'TUTORAPP01',
        ]);
    }

    public static function notifyAdmins(): void
    {
        $admins = User::where('is_active', true)->where(function ($query) {
            $query->where('role', 'admin')->orWhere('current_role', 'admin');
        })->get();
        foreach ($admins as $admin) {
            self::send($admin->user_id, 'มีคำขอเป็นติวเตอร์ใหม่ กรุณาตรวจสอบในเมนูคำขอติวเตอร์');
        }
    }
}
