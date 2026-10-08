<?php

namespace App\Domains\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;

/**
 * @property string $user_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string|null $role
 * @property bool|null $is_active
 * @property string|null $current_role
 * @property string|null $profile_picture
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read TutorProfile|null $tutorProfile
 * @property-read StudentProfile|null $studentProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Favorite> $favorites
 */
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, PasskeyAuthenticatable;

    protected $table = 'Users';
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
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * ทำงานอัตโนมัติก่อนที่จะบันทึกข้อมูลใหม่ลง Database
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->user_id)) {
                // สร้าง user_id อัตโนมัติ (เช่น USR-8F9A2B1C)
                $user->user_id = 'USR-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * ระบุ factory ตรง ๆ เพราะ model ถูกย้ายมาอยู่ใน Domains
     * Laravel จึงเดาชื่อ factory เองไม่ถูก
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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

    // ความสัมพันธ์กับโปรไฟล์ติวเตอร์ (ตาราง tutor_profiles ใช้คอลัมน์ Users_user_id)
    public function tutorProfile(): HasOne
    {
        return $this->hasOne(TutorProfile::class, 'Users_user_id', 'user_id');
    }

    // ความสัมพันธ์กับโปรไฟล์นักเรียน (ตาราง student_profiles ใช้คอลัมน์ user_id)
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id', 'user_id');
    }

    // รายการ Favorite ทั้งหมดที่ User คนนี้กดเซฟไว้
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }
}