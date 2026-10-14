<?php

namespace Database\Seeders;

use App\Domains\TutorProfile\Models\TutorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TutorProfilePhotoSeeder extends Seeder
{
    public function run(): void
    {
        // รูปตัวอย่างเก็บอยู่ใน public disk ใช้ชื่อบัญชีเพื่อค้นหาไฟล์ได้ง่าย
        $photos = [
            'DEMOTUT001' => 'tutor-profiles/ณัฐ-DEMOTUT001.jpg',
            'DEMOTUT002' => 'tutor-profiles/แบงค์-DEMOTUT002.jpg',
            'DEMOTUT003' => 'tutor-profiles/มายด์-DEMOTUT003.jpg',
            'TUT-92GZUNIW' => 'tutor-profiles/Test-TUT-92GZUNIW.jpg',
        ];

        foreach ($photos as $tutorId => $photo) {
            $tutorProfile = TutorProfile::with('user')->find($tutorId);
            if (! $tutorProfile || ! $tutorProfile->user) {
                continue;
            }

            // เก็บต้นฉบับไว้ในโปรเจกต์ เพื่อให้รัน seeder บนเครื่องอื่นได้
            $source = public_path('images/'.$photo);
            if (! Storage::disk('public')->exists($photo) && is_file($source)) {
                Storage::disk('public')->put($photo, file_get_contents($source));
            }
            if (! Storage::disk('public')->exists($photo)) {
                continue;
            }

            $user = $tutorProfile->user;
            // ไม่เขียนทับรูปที่ผู้ใช้เคยอัปโหลด และไม่เปลี่ยนผลอนุมัติ
            if (! $user->profile_picture) {
                $user->profile_picture = $photo;
                $user->save();
            }
        }
    }
}
