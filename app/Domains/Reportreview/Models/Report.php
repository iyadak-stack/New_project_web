<?php

namespace App\Domains\Reportreview\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'Report_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'Report_id',
        'review_id',
        'description',
        'status',
        'ReportReason_Reason_id',
        'Users_user_id',
        'handled_by',
        'handled_at',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'Users_user_id', 'user_id');
    }

    public function reason()
    {
        return $this->belongsTo(ReportReason::class, 'ReportReason_Reason_id', 'Reason_id');
    }

    public function evidences()
    {
        return $this->hasMany(ReportEvidence::class, 'Report_Report_id', 'Report_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id', 'Review_id')->withTrashed();
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by', 'user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }
}
