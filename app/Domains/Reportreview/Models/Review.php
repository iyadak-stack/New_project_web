<?php

namespace App\Domains\Reportreview\Models;

use App\Domains\Booking\Models\Appointment;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $table = 'reviews';

    protected $primaryKey = 'Review_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'Review_id',
        'rating',
        'Comment',
        'Appointment_Appointment_id',
        'Tutor_profiles_tutor_id',
    ];

    const DELETED_AT = 'deleted_at';

    public function appointment()
    {
        return $this->belongsTo(
            Appointment::class,
            'Appointment_Appointment_id',
            'Appointment_id'
        );
    }

    public function tutorProfile()
    {
        return $this->belongsTo(
            TutorProfile::class,
            'Tutor_profiles_tutor_id',
            'tutor_id'
        );
    }
}
