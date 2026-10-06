<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Reportreview\Models\Review;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'appointments';
    protected $primaryKey = 'Appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    protected $fillable = [
        'Appointment_id',
        'status',
        'start_datetime',
        'end_datetime',
        'Subject_subject_id',
        'Tutor_profiles_tutor_id',
        'Student_profiles_student_id',
    ];

    // ดึงข้อมูลวิชา
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'Subject_subject_id', 'subject_id');
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'Student_profiles_student_id', 'id');
    }

    public function tutorProfile()
    {
        return $this->belongsTo(TutorProfile::class, 'Tutor_profiles_tutor_id', 'id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'Appointment_Appointment_id', 'Appointment_id');
    }
}
