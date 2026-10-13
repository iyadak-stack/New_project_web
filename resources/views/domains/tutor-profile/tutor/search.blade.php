<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติวเตอร์ทั้งหมด / ผลการค้นหา - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main style="max-width: 1200px; margin: 32px auto; padding: 0 20px;">
        
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                🔍 {{ request('search') ? 'ผลการค้นหาติวเตอร์' : 'ติวเตอร์ทั้งหมดในระบบ' }}
            </h1>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                @if(request('search'))
                    ผลการค้นหาสำหรับคำว่า: <b style="color: #e07a5f;">"{{ request('search') }}"</b> (พบ {{ $tutors->count() }} รายการ)
                @else
                    ค้นหาและเลือกดูโปรไฟล์ติวเตอร์ที่ตรงกับความต้องการของคุณ (ทั้งหมด {{ $tutors->count() }} ท่าน)
                @endif
            </p>
        </div>

        @if ($tutors->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 24px;">
                @foreach ($tutors as $tutor)
                    @php
                        $tutorUser = $tutor->user;
                        $rating = number_format($tutor->average_rating ?? 0.0, 1);
                        $reviewCount = $tutor->reviews_count ?? ($tutor->reviews ? $tutor->reviews->count() : 0);
                    @endphp

                    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #fce7f3; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 12px;">
                        
                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 72px; height: 72px; border-radius: 16px; overflow: hidden; background: #fff0f3; flex-shrink: 0;">
                                @if ($tutorUser?->profile_picture)
                                    <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" alt="Tutor" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; font-size: 26px; font-weight: 700; background: #fca5a5; color: #fff; display: flex; align-items: center; justify-content: center;">
                                        {{ mb_substr($tutorUser?->first_name ?? 'P', 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">
                                    {{ $tutorUser?->first_name }} {{ $tutorUser?->last_name }}
                                </h3>

                                <p style="font-size: 14px; color: #64748b; margin: 0;">
                                    สอนพิเศษ {{ $tutor->experience_years ?? 0 }} ชั่วโมง
                                </p>

                                <div style="font-size: 14px; color: #e07a5f; font-weight: 600; margin-top: 2px;">
                                    ⭐ {{ $rating }} <span style="color: #64748b; font-weight: 400;">จาก {{ $reviewCount }} รีวิว</span>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 14px; color: #64748b;">
                            @if($tutor->subjects && $tutor->subjects->count() > 0)
                                {{ $tutor->subjects->pluck('subject_name')->implode(', ') }}
                            @else
                                ไม่มีรายละเอียดวิชา
                            @endif
                        </div>

                        <div>
                            @if ($tutor->approval_status === 'approved' && $tutor->user->is_active)
                            <a href="{{ route('tutor.show', $tutor->tutor_id) }}" style="display: inline-block; background: #e07a5f; color: #ffffff; padding: 8px 24px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                ดูโปรไฟล์ / จองเรียน
                            </a>
                            @else
                                {{-- แสดงได้ในหน้ารวม แต่ยังไม่เปิดโปรไฟล์สำหรับจองเรียน --}}
                                <span style="color: #64748b; font-size: 13px;">
                                    @if (!$tutor->user->is_active)
                                        บัญชีถูกระงับ
                                    @elseif ($tutor->approval_status === 'rejected')
                                        ยังไม่ผ่านอนุมัติ
                                    @else
                                        รอแอดมินอนุมัติ
                                    @endif
                                </span>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div style="border: 1px dashed #cbd5e1; border-radius: 16px; padding: 60px 20px; text-align: center; background: #ffffff; margin-bottom: 24px;">
                <div style="font-size: 40px; margin-bottom: 12px;">🔍</div>
                <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">ไม่พบผลการค้นหาติวเตอร์</h2>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 24px;">ลองค้นหาด้วยคำค้นอื่น เช่น ชื่อวิชา หรือชื่อติวเตอร์</p>
            </div>
        @endif

        <div style="margin-top: 16px;">
            <x-text-arrow-link :href="route('home')" label="กลับไปหน้าหลัก" direction="left" />
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
