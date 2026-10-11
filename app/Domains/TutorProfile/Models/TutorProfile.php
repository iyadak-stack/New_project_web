<?php

namespace App\Domains\TutorProfile\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Reportreview\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'average_rating',
        'teaching_mode',
        'line_id',
        'discord_id',
        'zoom_link',
        'approval_status',
    ];

    /**
     * เลือกติวเตอร์ที่แอดมินอนุมัติและบัญชียังใช้งานได้
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved')->whereHas('user', fn ($users) => $users->where('is_active', true));
    }

    // ต้องมีข้อมูลพื้นฐานและเลือกอย่างน้อยหนึ่งวิชาก่อนสมัคร
    public function isComplete(): bool
    {
        return filled($this->bio)
            && $this->experience_years !== null
            && in_array($this->teaching_mode, ['online', 'onsite', 'both'], true)
            && $this->subjects()->exists();
    }

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

    public function appointments()
    {
        return $this->hasMany(
            Appointment::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }

    public function reviews()
    {
        return $this->hasMany(
            Review::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }
}
