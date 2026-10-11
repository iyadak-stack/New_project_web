<?php

namespace App\Domains\Auth\Actions\Fortify;

use App\Domains\Auth\Concerns\PasswordValidationRules;
use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Services\TutorApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        session()->flash('is_register_error', true);
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'in:student,tutor'],
        ];
        // สมัครติวเตอร์ต้องกรอกข้อมูลชุดเดียวกับฟอร์มสลับบทบาท
        if (($input['role'] ?? '') === 'tutor') {
            $rules = array_merge($rules, TutorApplication::rules());
        }
        $data = Validator::make($input, $rules)->validate();

        // สร้างบัญชีและโปรไฟล์พร้อมกัน เพื่อไม่ให้เหลือบัญชีที่ข้อมูลไม่ครบ
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'user_id' => 'USR'.Str::upper(Str::random(7)),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'current_role' => $data['role'],
                'is_active' => true,
            ]);
            // ทุกบัญชีใช้บทบาทนักเรียนได้ แม้จะสมัครเป็นติวเตอร์
            StudentProfile::create(['id' => 'STU'.Str::upper(Str::random(7)), 'user_id' => $user->user_id]);
            if ($data['role'] === 'tutor') {
                TutorApplication::save($user, $data);
            }
            session()->forget('is_register_error');

            return $user;
        });
    }
}
