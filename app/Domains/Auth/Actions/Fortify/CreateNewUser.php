<?php

namespace App\Domains\Auth\Actions\Fortify;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\Auth\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // บันทึก Session เพื่อให้ Modal แสดงข้อผิดพลาดถูกฝั่ง
        session()->flash('is_register_error', true);

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => $this->passwordRules(),
            'role'       => ['nullable', 'string', 'in:student,tutor'],
            'line_id'    => ['nullable', 'string', 'max:255'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'zoom_link'  => ['nullable', 'url', 'max:255'],
        ])->validate();

        $user = User::create([
            'user_id'      => 'USR-' . strtoupper(Str::random(8)),
            'first_name'   => $input['first_name'],
            'last_name'    => $input['last_name'],
            'email'        => $input['email'],
            'role'         => $input['role'] ?? 'student',
            'current_role' => $input['role'] ?? 'student',
            'password'     => Hash::make($input['password']),
            'is_active'    => true,
        ]);

        // สร้าง Profile ตามบทบาทที่เลือก
        if ($user->role === 'tutor') {
            TutorProfile::create([
                'tutor_id' => $user->id,
                'user_id'    => $user->user_id,
                'line_id'    => $input['line_id'] ?? null,
                'discord_id' => $input['discord_id'] ?? null,
                'zoom_link'  => $input['zoom_link'] ?? null,
            ]);
        } else {
            StudentProfile::create([
                'user_id' => $user->user_id,
            ]);
        }

        return $user;
    }
}