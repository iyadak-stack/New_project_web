<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ผลการค้นหาติวเตอร์ - PeerTutor</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/home.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/tutor-navbar.css ต้องเป็น CSS/tutor-navbar.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}?v={{ time() }}">
    </head>
    <body>
        <!-- Header / Navbar ด้านบน --><x-home-navbar />
        <!-- Content หลัก --><main style="max-width: 1200px; margin: 32px auto; padding: 0 20px;">
            <form action="{{ route('tutor.search') }}" method="GET" class="search-box" style="margin:20px 0">
                <input name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์" aria-label="ค้นหาวิชา ติวเตอร์">
                <button>ค้นหา</button>
            </form>
            <!-- หัวข้อค้นหาติวเตอร์ --><div style="margin-bottom: 24px;">
                <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">🔍 ผลการค้นหาติวเตอร์</h1>
                <p style="font-size: 14px; color: #64748b; margin: 0;">
                    ผลการค้นหาสำหรับคำว่า:
                    <b style="color: #e07a5f;">
                        "{{ $search ?? request('search') }}"
                    </b>
                    (พบ {{ $tutors->count() }} รายการ)
                </p>
            </div>
            <!-- แสดงรายการติวเตอร์ การ์ดรูปแบบแนวนอน เหมือนหน้าแรก -->@if ($tutors->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 24px;">
                    @foreach ($tutors as $tutor)
                        @php
                        $tutorUser = $tutor->user;
                        $rating = number_format($tutor->average_rating ?? 0.0, 1);
                        $reviewCount = $tutor->reviews_count ?? ($tutor->reviews ? $tutor->reviews->count() : 0);
                    @endphp
                        <div style="background: #ffffff; border-radius: 20px; border: 1px solid #fce7f3; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 12px;">
                            <!-- ด้านบน: รูปฝั่งซ้าย + รายละเอียดฝั่งขวา --><div style="display: flex; gap: 16px; align-items: flex-start;">
                                <!-- รูปโปรไฟล์ติวเตอร์ --><div style="width: 72px; height: 72px; border-radius: 16px; overflow: hidden; background: #fff0f3; flex-shrink: 0;">
                                    @if ($tutorUser?->profile_picture)
                                        <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" alt="Tutor" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; font-size: 26px; font-weight: 700; background: #fca5a5; color: #fff; display: flex; align-items: center; justify-content: center;">
                                            {{ mb_substr($tutorUser?->first_name ?? 'P', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <!-- รายละเอียดชื่อ ประสบการณ์ และคะแนน --><div style="display: flex; flex-direction: column; gap: 4px;">
                                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">{{ $tutorUser?->first_name }} {{ $tutorUser?->last_name }}</h3>
                                    <p style="font-size: 14px; color: #64748b; margin: 0;">สอนพิเศษ {{ $tutor->experience_years ?? 0 }} ชั่วโมง</p>
                                    <div style="font-size: 14px; color: #e07a5f; font-weight: 600; margin-top: 2px;">
                                        {{ $rating }}
                                        <span style="color: #64748b; font-weight: 400;">จาก {{ $reviewCount }} รีวิว</span>
                                    </div>
                                </div>
                            </div>
                            <!-- วิชาที่สอน --><div style="font-size: 14px; color: #64748b;">
                                @if($tutor->subjects && $tutor->subjects->count() > 0)
                                    {{ $tutor->subjects->pluck('subject_name')->implode(', ') }}
                                @else
                                    ไม่มีรายละเอียด
                                @endif
                            </div>
                            <!-- ปุ่มดูโปรไฟล์ แบบแคปซูลมนสีส้ม --><div>
                                <a href="{{ route('tutor.show', $tutor->tutor_id) }}" style="display: inline-block; background: #e07a5f; color: #ffffff; padding: 8px 24px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s;">
                                    ดูโปรไฟล์
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- กรณีไม่พบผลการค้นหา เดิมตามรูปแรก --><div style="border: 1px dashed #cbd5e1; border-radius: 16px; padding: 60px 20px; text-align: center; background: #ffffff; margin-bottom: 24px;">
                    <div style="font-size: 40px; margin-bottom: 12px;">
                        🔍
                    </div>
                    <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">ไม่พบผลการค้นหาติวเตอร์</h2>
                    <p style="font-size: 14px; color: #64748b; margin-bottom: 24px;">ลองค้นหาด้วยคำค้นอื่น เช่น ชื่อวิชา หรือชื่อติวเตอร์</p>
                </div>
            @endif
            <!-- ปุ่มย้อนกลับ " กลับหน้าหลัก" ตามสไตล์รูปเดิม --><div style="margin-top: 16px;">
                <a href="{{ route('home') }}" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">← กลับหน้าหลัก</a>
            </div>
        </main>
        <!-- สคริปต์เปิด/ปิด Pop-over Profile Card --><script>
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
