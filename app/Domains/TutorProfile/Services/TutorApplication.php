<?php

namespace App\Domains\TutorProfile\Services;

use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Services\UserNotification;
use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TutorApplication
{
    public static function rules(): array
    {
        return [
            'bio' => ['required', 'string', 'max:3000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'teaching_mode' => ['required', 'in:online,onsite,both'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['required', 'distinct', 'exists:subjects,subject_id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'line_id' => ['nullable', 'string', 'max:45'],
            'discord_id' => ['nullable', 'string', 'max:255'],
            'zoom_link' => ['nullable', 'url:http,https', 'max:255'],
            'google_meet_link' => ['nullable', 'url:http,https', 'max:255'],
            'meeting_location' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function save(User $user, array $data): TutorProfile
    {
        // บันทึกโปรไฟล์ วิชา และข้อมูลติดต่อพร้อมกัน หากผิดพลาดให้ยกเลิกทั้งหมด
        return DB::transaction(function () use ($user, $data) {
            User::whereKey($user->user_id)->lockForUpdate()->firstOrFail();
            // ใช้โปรไฟล์เดิมเมื่อเคยสมัครแล้ว เพื่อไม่สร้างข้อมูลติวเตอร์ซ้ำ
            $profile = TutorProfile::where('Users_user_id', $user->user_id)
                ->lockForUpdate()
                ->first();

            if (! $profile) {
                $profile = new TutorProfile;
                $profile->Users_user_id = $user->user_id;
            }
            if (! $profile->exists) {
                $profile->tutor_id = 'TUT'.Str::upper(Str::random(7));
            }
            $profile->bio = $data['bio'];
            $profile->experience_years = $data['experience_years'];
            $profile->teaching_mode = $data['teaching_mode'];

            // ผู้ที่ผ่านแล้วแก้โปรไฟล์ได้ ส่วนใบสมัครใหม่หรือไม่ผ่านต้องให้แอดมินตรวจ

            $needsReview = $profile->approval_status !== 'approved';
            if ($needsReview) {
                $profile->approval_status = 'pending';
                $profile->submitted_at = now();
                $profile->reviewed_at = null;
                $profile->reviewed_by = null;
                $profile->reject_reason = null;
            }
            $profile->save();
            $profile->subjects()->sync($data['subjects']);
            $contact = Contact::firstOrNew(['Users_user_id' => $user->user_id]);
            if (! $contact->exists) {
                $contact->contact_id = 'CON'.Str::upper(Str::random(7));
            }
            // แก้เฉพาะช่องที่ส่งมา ช่องอื่นให้คงข้อมูลเดิมไว้
            $contactFields = ['line_id', 'discord_id', 'zoom_link', 'google_meet_link', 'meeting_location'];
            foreach ($contactFields as $field) {
                if (array_key_exists($field, $data)) {
                    $contact->$field = $data[$field];
                }
            }
            $contact->save();
            if (! empty($data['avatar'])) {
                $user->profile_picture = $data['avatar']->store('avatars', 'public');
                $user->save();
            }
            if ($needsReview) {
                foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
                    UserNotification::send($admin->user_id, 'มีใบสมัครติวเตอร์รอตรวจสอบ: '.$user->first_name.' '.$user->last_name);
                }
            }

            return $profile;
        });
    }
}
