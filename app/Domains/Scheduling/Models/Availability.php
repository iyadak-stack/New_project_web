<?php

namespace App\Domains\Scheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Auth\Models\User;

class Availability extends Model
{
    protected $primaryKey = 'availability_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'availability_id',
        'user_id',
        'start_datetime',
        'end_datetime',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}