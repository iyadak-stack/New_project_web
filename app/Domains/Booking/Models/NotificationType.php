<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;

    protected $table = 'notification_types';
    protected $primaryKey = 'notification_type_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_type_id',
        'type_name',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'NotificationType_notification_type_id', 'notification_type_id');
    }
}