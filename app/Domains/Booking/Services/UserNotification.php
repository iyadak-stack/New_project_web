<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Notification;
use App\Domains\Booking\Models\NotificationType;
use Illuminate\Support\Str;

class UserNotification
{
    public static function send(string $userId, string $message): void
    {
        NotificationType::firstOrCreate(['notification_type_id' => 'TYPE03'], ['type_name' => 'ระบบติวเตอร์']);
        Notification::create([
            'notification_id' => Str::upper(Str::random(10)),
            'message' => $message,
            'is_read' => false,
            'Users_user_id' => $userId,
            'NotificationType_notification_type_id' => 'TYPE03',
        ]);
    }
}
