<?php

namespace App\Domains\TutorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Domains\Auth\Models\User;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    // ความสัมพันธ์: Favorite เป็นของ User คนที่กดเซฟ[cite: 1]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ความสัมพันธ์แบบ Polymorphic (ชี้ได้ทั้ง User/Tutor และ Subject ของทับทิม)[cite: 1]
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }
}