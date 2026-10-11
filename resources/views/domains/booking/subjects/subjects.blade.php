<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายวิชาทั้งหมด - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="page">
        <!-- Header ส่วนหัว -->
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                    📚 รายวิชาทั้งหมดในระบบ
                </h1>
                <p style="color: #64748b; font-size: 14px; margin: 0;">
                    สำรวจรายวิชาที่คุณสนใจ เพื่อเลือกดูติวเตอร์ที่พร้อมสอนในวิชานั้นๆ
                </p>
            </div>

            <button type="button" onclick="window.history.back()" class="btn" style="width: auto; padding: 10px 20px; background-color: #f1f5f9; color: #475569; font-weight: 500; cursor: pointer; border: none;">
                ← ย้อนกลับ
            </button>
        </div>

        <!-- Grid แสดงรายวิชาทั้งหมด -->
        <div class="grid">
            @if(isset($subjects) && count($subjects) > 0)
                @foreach($subjects as $subject)
                    @php
                        $title = $subject->subject_name ?? $subject->title ?? '';
                        $count = $subject->tutors_count ?? 0;
                        $description = $subject->description ?? 'รายวิชาคุณภาพเยี่ยม';
                    @endphp
                    <div class="card card-subject">
                        <div class="subject-top">
                            <h3>{{ $title }}</h3>
                            <span class="count">{{ $count }}+ ติวเตอร์</span>
                        </div>

                        <p class="desc">{{ $description }}</p>

                        <a href="{{ route('subjects.show', $subject->subject_id) }}" class="btn">
                            ดูติวเตอร์วิชานี้
                        </a>
                    </div>
                @endforeach
            @else
                <p style="color: #94a3b8; font-size: 14px; grid-column: 1 / -1;">ยังไม่มีข้อมูลรายวิชาในระบบ</p>
            @endif
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