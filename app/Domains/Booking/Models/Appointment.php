<?php

namespace App\Domains\Booking\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Review;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'lesson_details_customized',
        'meeting_zoom', 'meeting_meet', 'meeting_location', 'lesson_notes', 'rejection_reason',
        'start_datetime',
        'end_datetime',
        'Subject_subject_id',
        'Tutor_profiles_tutor_id',
        'Student_profiles_student_id',
    ];

    protected function casts(): array
    {
        return [
            'lesson_details_customized' => 'boolean',
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
        ];
    }

    // เลือกข้อมูลห้องเรียนของนัดหมายก่อน ถ้ายังไม่มีจึงใช้ข้อมูลจากโปรไฟล์
    public function lessonValue(string $field): string
    {
        $appointmentValue = $this->getAttribute($field);

        // ถ้าติวเตอร์ล้างช่องนี้เอง ต้องคงค่าว่างไว้ ไม่ดึงข้อมูลเดิมกลับมา
        if ($this->lesson_details_customized) {
            return (string) ($appointmentValue ?? '');
        }

        if (! empty($appointmentValue)) {
            return (string) $appointmentValue;
        }

        $contactFields = [
            'meeting_zoom' => 'zoom_link',
            'meeting_meet' => 'google_meet_link',
            'meeting_location' => 'meeting_location',
        ];
        $contactField = $contactFields[$field];
        $contact = $this->tutorProfile?->user?->contact;

        if (! empty($contact?->$contactField)) {
            return (string) $contact->$contactField;
        }

        // รองรับข้อมูลเก่าที่เคยเก็บช่องติดต่อไว้ในโปรไฟล์ติวเตอร์
        return (string) ($this->tutorProfile?->$contactField ?? '');
    }

    // ยืนยันเรียนจบได้เมื่อรับคำขอแล้ว และผ่านเวลาสิ้นสุดของนัดหมาย
    public function canComplete(): bool
    {
        return $this->status === 'confirmed' && $this->end_datetime !== null && $this->end_datetime->isPast();
    }

    // ดึงข้อมูลวิชา
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'Subject_subject_id', 'subject_id');
    }

    public function tutorProfile(): BelongsTo
    {
        return $this->belongsTo(TutorProfile::class, 'Tutor_profiles_tutor_id', 'tutor_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'Student_profiles_student_id', 'id');
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Student_profiles_student_id', 'user_id');
    }

    public function loadStudentProfile(): void
    {
        // ทำให้หน้าจอเดิมอ่านผู้เรียนได้ทั้งนัดหมายเก่าและของ branch เพื่อน
        if (! $this->studentProfile && $this->studentUser) {
            $this->setRelation('studentProfile', $this->studentUser->studentProfile);
        }
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'Appointment_Appointment_id', 'Appointment_id');
    }
}
