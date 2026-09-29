<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_id',
        'message',
        'is_read',
        'Users_user_id',
        'NotificationType_notification_type_id',
    ];

    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class, 'NotificationType_notification_type_id', 'notification_type_id');
    }
}