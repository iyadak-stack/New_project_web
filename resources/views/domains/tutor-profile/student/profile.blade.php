<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์นักเรียน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

@php
    $user = auth()->user();
    $avatarUrl = $user?->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : ($user?->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode(($user?->first_name ?? 'Student') . ' ' . ($user?->last_name ?? '')) . '&color=e07a5f&background=fdeee8');

    $contact = $user?->contact;
@endphp

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="pe-page" style="max-width: 680px; margin: 40px auto; padding: 0 16px;">
        <div class="pe-card" style="background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">

            @if (session('success'))
                <div style="background-color: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <!-- 1. ส่วนหัวโปรไฟล์ -->
            <div style="text-align: center; padding-bottom: 24px; border-bottom: 1px solid #f1f5f9;">
                <div style="width: 110px; height: 110px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; border: 3px solid #e07a5f; box-shadow: 0 4px 10px rgba(224,122,95,0.2);">
                    <img src="{{ $avatarUrl }}" alt="Profile Image" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h1 style="font-size: 22px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                    คุณ{{ $user?->first_name }} {{ $user?->last_name }}
                </h1>
                <span style="display: inline-block; background-color: #fdeee8; color: #e07a5f; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                    🎓 นักเรียน
                </span>
            </div>

            <!-- 2. ประวัติส่วนตัว -->
            <div style="margin-top: 24px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    📝 ประวัติส่วนตัว (Bio)
                </h2>
                <div style="background-color: #f8fafc; padding: 16px; border-radius: 12px; color: #334155; font-size: 14px; line-height: 1.6; border: 1px solid #e2e8f0;">
                    {{ $studentProfile?->bio ?: 'ยังไม่มีข้อมูลประวัติส่วนตัว' }}
                </div>
            </div>

            <!-- 3. วิชาที่สนใจ -->
            <div style="margin-top: 24px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    📘 วิชาที่สนใจอยากเรียน
                </h2>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @if ($studentProfile && $studentProfile->subjects && $studentProfile->subjects->count() > 0)
                        @foreach ($studentProfile->subjects as $subject)
                            <span style="background-color: #eff6ff; color: #2563eb; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; border: 1px solid #bfdbfe;">
                                {{ $subject->subject_name }}
                            </span>
                        @endforeach
                    @else
                        <span style="color: #94a3b8; font-size: 14px;">ยังไม่ได้เลือกวิชาที่สนใจ</span>
                    @endif
                </div>
            </div>

            <!-- 4. ช่องทางการติดต่อ -->
            <div style="margin-top: 24px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    📞 ช่องทางการติดต่อ
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                    <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #94a3b8; display: block;">Line ID</span>
                        <strong style="color: #059669; font-size: 14px;">{{ $contact?->line_id ?? '-' }}</strong>
                    </div>
                    <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #94a3b8; display: block;">Discord ID</span>
                        <strong style="color: #5865f2; font-size: 14px;">{{ $contact?->discord_id ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <!-- 5. ปุ่มแอคชัน -->
            <div style="margin-top: 32px; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('student.profile.edit') }}" style="background-color: #e07a5f; color: #ffffff; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">
                    ✏️ แก้ไขโปรไฟล์
                </a>
                <a href="{{ route('home') }}" style="background-color: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">
                    กลับหน้าแรก
                </a>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggleProfileBtn');
            const dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>