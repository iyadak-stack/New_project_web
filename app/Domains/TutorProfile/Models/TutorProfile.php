<?php

namespace App\Domains\TutorProfile\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Reportreview\Models\Review;
use App\Domains\Scheduling\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorProfile extends Model
{
    protected $table = 'tutor_profiles';

    protected $primaryKey = 'tutor_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'Users_user_id',
        'tutor_id',
        'bio',
        'experience_years',
        // ระบบรีวิวของเพื่อนอัปเดตคะแนนเฉลี่ยผ่าน Model นี้
        'average_rating',
        'teaching_mode',
        'line_id',
        'discord_id',
        'zoom_link',
        'approval_status',
        'submitted_at',
        'reject_reason',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    // ใช้ในหน้าค้นหาและหน้ารวม เพื่อแสดงเฉพาะติวเตอร์ที่อนุมัติแล้ว
    public function scopeApproved($query)
    {
        // บัญชีที่ถูกพักไม่ควรแสดงเป็นติวเตอร์พร้อมรับเรียน
        return $query->where('approval_status', 'approved')->whereHas('user', function ($users) {
            $users->where('is_active', true);
        });
    }

    // ใช้เฉพาะรายการเลือกผู้สอน ไม่กระทบโปรไฟล์ส่วนตัวหรือนัดหมายเดิม
    public function scopeForStudentList($query)
    {
        $user = auth()->user();
        if ($user && $user->current_role === 'student') {
            $query->where('tutor_profiles.Users_user_id', '!=', $user->user_id);
        }

        return $query;
    }

    /**
     * 🟢 ระบุให้ Route Model Binding ค้นหาจากคอลัมน์ tutor_id
     */
    public function getRouteKeyName()
    {
        return 'tutor_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'Users_user_id',
            'user_id'
        );
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'tutor_profiles_has_subjects',
            'Tutor_profiles_tutor_id',
            'Subject_subject_id',
            'tutor_id',
            'subject_id'
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(
            Review::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        )
            ->withTrashed()
            ->whereNull('reviews.deleted_at');
    }

    /**
     * 📅 ความสัมพันธ์ตารางตารางเวลา/ความพร้อมสอน (Scheduling Domain)
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(
            Availability::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }
}
