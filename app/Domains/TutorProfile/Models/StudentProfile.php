<?php

namespace App\Domains\TutorProfile\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Subject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

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
        // เช็กชื่อตาราง Pivot จริงใน Database อัตโนมัติ (รองรับทุกชื่อที่เป็นไปได้)
        $pivotTable = 'student_profiles_has_subjects';
        
        if (Schema::hasTable('student_profiles_has_subject')) {
            $pivotTable = 'student_profiles_has_subject';
        } elseif (Schema::hasTable('student_profile_subject')) {
            $pivotTable = 'student_profile_subject';
        } elseif (Schema::hasTable('Student_profiles_has_Subject')) {
            $pivotTable = 'Student_profiles_has_Subject';
        }

        // กำหนด Foreign Key ให้สัมพันธ์กับชื่อตาราง
        $foreignKey = str_contains($pivotTable, 'Student_profiles') 
            ? 'student_profiles_student_id' 
            : ($pivotTable === 'student_profile_subject' ? 'student_profile_id' : 'student_id');

        $relatedKey = str_contains($pivotTable, 'Subject') 
            ? 'subject_subject_id' 
            : 'subject_id';

        return $this->belongsToMany(
            Subject::class,
            $pivotTable,
            $foreignKey,
            $relatedKey,
            'id',
            'subject_id'
        );
    }
}