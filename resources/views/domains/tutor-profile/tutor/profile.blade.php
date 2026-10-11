@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'โปรไฟล์ติวเตอร์ - PeerTutor')

@section('content')

@php
    $user = $user ?? auth()->user();
    $avatarUrl = $user?->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : ($user?->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode(($user?->first_name ?? 'Tutor') . ' ' . ($user?->last_name ?? '')) . '&color=e07a5f&background=fdeee8');

    $contact = $contact ?? $user?->contact ?? \App\Domains\Auth\Models\Contact::where('Users_user_id', $user?->user_id)->first();
@endphp

    <div style="padding: 12px 8px;">

        @if (session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- 1. ส่วนหัวโปรไฟล์ส่วนตัว (User Info) -->
        <div style="text-align: center; padding-bottom: 24px; border-bottom: 1px solid #f1f5f9; margin-bottom: 24px;">
            <div style="width: 100px; height: 100px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; border: 3px solid #e07a5f; box-shadow: 0 4px 10px rgba(224,122,95,0.2);">
                <img src="{{ $avatarUrl }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                คุณ {{ $user?->first_name }} {{ $user?->last_name }}
            </h1>
            <span style="display: inline-block; background-color: #fdeee8; color: #e07a5f; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                👨‍🏫 ติวเตอร์
            </span>
        </div>

        @if ($tutorProfile)
            <!-- 2. ข้อมูลการสอน (Teaching Info) -->
            <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                📌 ข้อมูลการสอน
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px;">
                <div style="background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 2px;">ประสบการณ์</span>
                    <strong style="font-size: 16px; color: #1e293b;">{{ $tutorProfile->experience_years ?? 0 }} ปี</strong>
                </div>

                <div style="background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 2px;">คะแนนรีวิว</span>
                    <strong style="font-size: 16px; color: #d97706;">⭐ {{ number_format($tutorProfile->average_rating ?? 0, 2) }} / 5.00</strong>
                </div>

                <div style="background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 2px;">รูปแบบการสอน</span>
                    <strong style="font-size: 15px; color: #1e293b;">
                        @if ($tutorProfile->teaching_mode === 'online') ออนไลน์
                        @elseif ($tutorProfile->teaching_mode === 'onsite') นัดเจอ (Onsite)
                        @else ออนไลน์ & Onsite
                        @endif
                    </strong>
                </div>
            </div>

            <!-- 3. แนะนำตัว (Bio) -->
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 8px;">
                    📝 แนะนำตัว (Bio)
                </h2>
                <div style="background-color: #f8fafc; padding: 16px; border-radius: 12px; color: #334155; font-size: 14px; line-height: 1.6; border: 1px solid #e2e8f0;">
                    {{ $tutorProfile->bio ?: 'ยังไม่ได้เพิ่มข้อมูลแนะนำตัว' }}
                </div>
            </div>

            <!-- 4. วิชาที่เปิดสอน (Subjects) -->
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    📘 วิชาที่เปิดสอน
                </h2>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 48px; align-items: center;">
                    @if ($tutorProfile->subjects && $tutorProfile->subjects->count() > 0)
                        @foreach ($tutorProfile->subjects as $subject)
                            <span style="background-color: #fdeee8; color: #e07a5f; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: 500; border: 1px solid #f4a261; display: inline-flex; align-items: center; gap: 6px;">
                                📘 {{ $subject->subject_name }}
                            </span>
                        @endforeach
                    @else
                        <span style="color: #94a3b8; font-size: 14px;">ยังไม่ได้ระบุวิชาที่เปิดสอน</span>
                    @endif
                </div>
            </div>

            <!-- 5. ช่องทางการติดต่อในการสอน -->
            <div style="margin-bottom: 28px;">
                <h2 style="font-size: 15px; font-weight: 600; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    📞 ช่องทางติดต่อในการสอน
                </h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                    <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #94a3b8; display: block;">Line ID</span>
                        <strong style="color: #059669; font-size: 14px;">{{ $contact?->line_id ?: ($tutorProfile->line_id ?: '-') }}</strong>
                    </div>

                    <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #94a3b8; display: block;">Discord ID</span>
                        <strong style="color: #5865f2; font-size: 14px;">{{ $contact?->discord_id ?: ($tutorProfile->discord_id ?: '-') }}</strong>
                    </div>

                    <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #94a3b8; display: block;">Zoom Link</span>
                        @if ($contact?->zoom_link || $tutorProfile->zoom_link)
                            <a href="{{ $contact?->zoom_link ?: $tutorProfile->zoom_link }}" target="_blank" rel="noopener" style="color: #2563eb; font-size: 14px; text-decoration: underline; font-weight: 500; word-break: break-all;">
                                เปิดลิงก์ Zoom
                            </a>
                        @else
                            <strong style="color: #64748b; font-size: 14px;">-</strong>
                        @endif
                    </div>
                </div>
            </div>

        @else
            <div style="background: #f8fafc; padding: 24px; border-radius: 12px; text-align: center; margin-bottom: 24px; border: 1px solid #e2e8f0;">
                <p style="margin: 0; color: #64748b; font-size: 14px;">คุณยังไม่ได้ตั้งค่าโปรไฟล์ติวเตอร์</p>
            </div>
        @endif

        <!-- 6. ปุ่มแอคชัน -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
            <a href="{{ route('tutor.profile.edit') }}" style="background-color: #e07a5f; color: #ffffff; padding: 10px 22px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block;">
                ✏️ แก้ไขโปรไฟล์
            </a>
            <a href="{{ route('home') }}" style="background-color: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">
                กลับหน้าหลัก
            </a>
        </div>
    </div>

@endsection
