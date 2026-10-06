<?php

namespace App\Domains\TutorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;

class TutorProfile extends Model
{
    protected $table = 'tutor_profiles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'average_rating',
        'teaching_mode',
    ];

    // ความสัมพันธ์: TutorProfile เป็นของ User 1 คน
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'Tutor_profiles_has_Subject',
            'Tutor_profiles_tutor_id',
            'Subject_subject_id',
            'id',
            'Subjec_id'
        );
    }
}
