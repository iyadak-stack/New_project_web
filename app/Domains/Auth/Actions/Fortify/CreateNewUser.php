<?php

namespace App\Domains\Auth\Actions\Fortify;

use App\Domains\Auth\Concerns\PasswordValidationRules;
use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\StudentProfile;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\TutorProfile\Services\TutorApplicationNotification;
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
        $isTutor = ($input['role'] ?? 'student') === 'tutor';
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'in:student,tutor'],
            'bio' => [$isTutor ? 'required' : 'nullable', 'string', 'max:3000'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'teaching_mode' => [$isTutor ? 'required' : 'nullable', 'in:online,onsite,both'],
            'subjects' => [$isTutor ? 'required' : 'nullable', 'array', 'min:1'],
            'subjects.*' => ['string', 'distinct', 'exists:subjects,subject_id'],
            'line_id' => ['nullable', 'string', 'max:45'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'zoom_link' => ['nullable', 'url', 'starts_with:http://,https://', 'max:255'],
            'google_meet_link' => ['nullable', 'url', 'starts_with:http://,https://', 'max:255'],
        ])->validate();

        // สร้างบัญชี โปรไฟล์ และคำขอพร้อมกัน ถ้าผิดพลาดจะไม่บันทึกบางส่วน
        return DB::transaction(function () use ($input, $isTutor) {
            $user = User::create([
                'user_id' => strtoupper(Str::random(10)),
                'first_name' => $input['first_name'], 'last_name' => $input['last_name'],
                'email' => $input['email'], 'role' => $input['role'] ?? 'student',
                // สมัครติวเตอร์ก็ยังใช้บทบาทนักเรียนจนกว่าแอดมินอนุมัติ
                'current_role' => 'student', 'password' => Hash::make($input['password']),
                'is_active' => true,
            ]);
            StudentProfile::create(['user_id' => $user->user_id]);
            if ($isTutor) {
                $profile = TutorProfile::create([
                    'tutor_id' => strtoupper(Str::random(10)), 'Users_user_id' => $user->user_id,
                    'bio' => $input['bio'], 'experience_years' => $input['experience_years'] ?? 0,
                    'teaching_mode' => $input['teaching_mode'],
                    'approval_status' => 'pending', 'submitted_at' => now(),
                ]);
                $profile->subjects()->sync($input['subjects']);
                Contact::create([
                    'contact_id' => strtoupper(Str::random(10)), 'Users_user_id' => $user->user_id,
                    'line_id' => $input['line_id'] ?? null, 'discord_id' => $input['discord_id'] ?? null,
                    'zoom_link' => $input['zoom_link'] ?? null, 'google_meet_link' => $input['google_meet_link'] ?? null,
                ]);
                TutorApplicationNotification::notifyAdmins();
                TutorApplicationNotification::send($user->user_id, 'ส่งคำขอเป็นติวเตอร์แล้ว กรุณารอแอดมินอนุมัติ');
            }

            return $user;
        });
    }
}
