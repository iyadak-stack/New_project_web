<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject->subject_name }} - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Navbar -->
    <x-home-navbar />

    <!-- Main Content -->
    <main style="max-width: 1200px; margin: 32px auto; padding: 0 20px;">

        <!-- Header กล่องข้อมูลวิชา -->
        <div style="background: linear-gradient(135deg, #fff5f2 0%, #ffffff 100%); border-radius: 20px; border: 1px solid #fdeee8; padding: 28px; margin-bottom: 32px; box-shadow: 0 4px 15px rgba(224, 122, 95, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="font-size: 28px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
                        📚 {{ $subject->subject_name }}
                    </h1>
                    <p style="font-size: 15px; color: #64748b; margin: 0; max-width: 700px; line-height: 1.6;">
                        {{ $subject->description ?? 'รายละเอียดวิชาคุณภาพสำหรับเสริมสร้างความรู้ความเข้าใจอย่างเจาะลึก' }}
                    </p>
                </div>

                <button type="button" onclick="window.history.back()" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 12px; font-size: 14px; font-weight: 500; cursor: pointer;">
                    ← ย้อนกลับ
                </button>
            </div>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #fdeee8; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: #e07a5f; background: #fdeee8; padding: 6px 16px; border-radius: 20px;">
                    🎯 มีติวเตอร์พร้อมสอน {{ $subject->tutors ? $subject->tutors->count() : 0 }} ท่าน
                </span>
            </div>
        </div>

        <!-- รายชื่อติวเตอร์ -->
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">
                👨‍🏫 รายชื่อติวเตอร์ที่เปิดสอนวิชานี้
            </h2>
        </div>

        @if($subject->tutors && $subject->tutors->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                @foreach($subject->tutors as $tutor)
                    @php
                        $tutorUser = $tutor->user;
                        $rating = number_format($tutor->average_rating ?? 0.0, 1);
                        $reviewCount = $tutor->reviews_count ?? ($tutor->reviews ? $tutor->reviews->count() : 0);
                    @endphp

                    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #fce7f3; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between; gap: 16px;">

                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; overflow: hidden; background: #fff0f3; flex-shrink: 0;">
                                @if ($tutorUser?->profile_picture)
                                    <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" alt="Tutor" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; font-size: 22px; font-weight: 700; background: #fca5a5; color: #fff; display: flex; align-items: center; justify-content: center;">
                                        {{ mb_substr($tutorUser?->first_name ?? 'P', 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                    {{ $tutorUser?->first_name }} {{ $tutorUser?->last_name }}
                                </h3>
                                <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">
                                    ประสบการณ์ {{ $tutor->experience_years ?? 0 }} ชั่วโมง
                                </p>
                                <div style="font-size: 13px; color: #e07a5f; font-weight: 600;">
                                    ⭐ {{ $rating }} <span style="color: #94a3b8; font-weight: 400;">({{ $reviewCount }} รีวิว)</span>
                                </div>
                            </div>
                        </div>

                        <div style="background: #f8fafc; padding: 8px 12px; border-radius: 8px; font-size: 12px; color: #475569;">
                            📍 รูปแบบ: {{ $tutor->teaching_mode === 'onsite' ? 'สอนตัวต่อตัว (Onsite)' : ($tutor->teaching_mode === 'both' ? 'ออนไลน์ & Onsite' : 'ออนไลน์ (Online)') }}
                        </div>

                        <a href="{{ route('tutor.show', $tutor->tutor_id) }}" style="display: block; text-align: center; background: #e07a5f; color: #ffffff; padding: 10px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 600;">
                            ดูโปรไฟล์ / จองเรียน
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 20px; text-align: center;">
                <div style="font-size: 36px; margin-bottom: 12px;">🔍</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">ยังไม่มีติวเตอร์เปิดสอนวิชานี้</h3>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 16px;">ลองเลือกดูรายวิชาอื่น หรือกลับมาเช็กใหม่อีกครั้งในภายหลัง</p>
                <button type="button" onclick="window.history.back()" style="background: #e07a5f; color: #fff; border: none; padding: 8px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer;">
                    ← ย้อนกลับ
                </button>
            </div>
        @endif

    </main>


</body>
</html>
