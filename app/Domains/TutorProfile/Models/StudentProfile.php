<?php

namespace App\Domains\TutorProfile\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentProfile extends Model
{
    use HasUuids;

    protected $table = 'student_profiles';

    protected $fillable = [
        'id',
        'user_id',
        'bio',
    ];

    /**
     * Relationship ไปยังตาราง Users
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship ไปยังตาราง Subject ผ่านตาราง Pivot
     */
    public function subjects(): BelongsToMany
    {
        // ตารางเดิมใช้ id เป็นคีย์ของนักเรียน ส่วนชื่อคอลัมน์ Pivot คงตาม migration
        return $this->belongsToMany(
            Subject::class,
            'Student_profiles_has_Subject',
            'Student_profiles_student_id',
            'Subject_subject_id',
            'id',
            'subject_id'
        )->withTimestamps();
    }
}
