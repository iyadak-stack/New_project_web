<?php

namespace App\Domains\TutorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;

class Favorite extends Model
{
    use SoftDeletes;

    protected $table = 'favorites';
    protected $primaryKey = 'favorite_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'favorite_id',
        'Users_user_id',
        'Tutor_profiles_tutor_id',
        'Subject_subject_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'Users_user_id',
            'user_id'
        );
    }

    public function tutorProfile(): BelongsTo
    {
        return $this->belongsTo(
            TutorProfile::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(
            Subject::class,
            'Subject_subject_id',
            'subject_id'
        );
    }
}