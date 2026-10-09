<?php

namespace App\Domains\TutorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;

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
        'teaching_mode',
        'line_id',
        'discord_id',
        'zoom_link',
        'approval_status',
    ];

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
        )
        ->withTrashed()
        ->whereNull('reviews.deleted_at');
    }
}