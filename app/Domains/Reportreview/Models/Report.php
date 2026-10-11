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
        'Report_id',
        'description',
        'status',
        'ReportReason_Reason_id',
        'Users_user_id',
        'Reported_users_user_id',
    ];

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'ReportReason_Reason_id', 'Reason_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Users_user_id', 'user_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Reported_users_user_id', 'user_id');
    }

    public function tutorProfile(): BelongsTo
    {
        return $this->belongsTo(TutorProfile::class, 'Reported_users_user_id', 'Users_user_id');
    }
}
