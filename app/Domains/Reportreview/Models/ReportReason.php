<?php

namespace App\Domains\Reportreview\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    protected $table = 'report_reasons';
    protected $primaryKey = 'Reason_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['Reason_id', 'reason_name'];

    public function reports()
    {
        return $this->hasMany(Report::class, 'ReportReason_Reason_id', 'Reason_id');
    }
}
