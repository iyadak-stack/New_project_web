<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domains\TutorProfile\Models\TutorProfile;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';

    protected $primaryKey = 'subject_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'subject_id',
        'subject_name',
    ];

    // ความสัมพันธ์กับ Appointment
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'Subject_subject_id', 'subject_id');
    }

    // ความสัมพันธ์กับ TutorProfile (ผ่านตารางกลาง)
    public function tutors(): BelongsToMany
    {
        return $this->belongsToMany(
            TutorProfile::class,
            'tutor_profiles_has_subjects',
            'Subject_subject_id',
            'Tutor_profiles_tutor_id',
            'subject_id',
            'tutor_id'
        );
    }
}