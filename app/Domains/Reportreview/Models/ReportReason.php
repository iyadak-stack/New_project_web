<?php

namespace App\Domains\Reportreview\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    protected $table = 'report_reasons';
    protected $primaryKey = 'Reason_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['Reason_id', 'reason_name'];
}
