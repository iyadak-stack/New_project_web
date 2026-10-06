<?php

namespace App\Domains\Reportreview\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEvidence extends Model
{
    protected $table = 'report_evidences';
    protected $primaryKey = 'FilePath';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['FilePath', 'file_type', 'Report_Report_id'];
}
