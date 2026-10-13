<?php

namespace App\Domains\Reportreview\Models;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'Report_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'Report_id', 'description', 'status', 'ReportReason_Reason_id',
        'Users_user_id', 'Tutor_profiles_tutor_id',
    ];

    // เชื่อมรายงานกับประเภท ผู้ส่ง และติวเตอร์ที่ถูกรายงาน
    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'ReportReason_Reason_id', 'Reason_id');
    }

    public function reporter(): BelongsTo
    {
        // ยังแสดงผู้ส่งในประวัติรายงานได้ แม้บัญชีถูก soft delete
        return $this->belongsTo(User::class, 'Users_user_id', 'user_id')->withTrashed();
    }

    public function tutorProfile(): BelongsTo
    {
        return $this->belongsTo(TutorProfile::class, 'Tutor_profiles_tutor_id', 'tutor_id');
    }
}
