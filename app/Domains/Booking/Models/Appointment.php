<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'appointments';
    protected $primaryKey = 'Appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';

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
}