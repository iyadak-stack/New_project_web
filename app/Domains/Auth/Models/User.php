<?php

namespace App\Domains\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;

use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\Favorite;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, PasskeyAuthenticatable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'current_role',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->first_name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    // ความสัมพันธ์กับโปรไฟล์ติวเตอร์
    public function tutorProfile(): HasOne
    {
        return $this->hasOne(TutorProfile::class, 'user_id', 'user_id');
    }

    // ความสัมพันธ์กับโปรไฟล์นักเรียน
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id', 'user_id');
    }

    // รายการ Favorite ทั้งหมดที่ User คนนี้กดเซฟไว้
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'Users_user_id', 'user_id');
    }
}
