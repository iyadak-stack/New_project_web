<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติวเตอร์ทั้งหมด - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="page">
        <!-- Header ส่วนหัว -->
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                    🎓 ติวเตอร์ทั้งหมดในระบบ
                </h1>
                <p style="color: #64748b; font-size: 14px; margin: 0;">
                    @if (request('search'))
                        ผลการค้นหา "<b>{{ request('search') }}</b>" พบ {{ count($tutors) }} คน
                    @else
                        เลือกติวเตอร์ที่ใช่ แล้วเริ่มเรียนกับรุ่นพี่ที่เข้าใจเนื้อหาเดียวกันได้เลย
                    @endif
                </p>
            </div>

        </div>

        <!-- Grid แสดงติวเตอร์ทั้งหมด -->
        <div class="grid">
            @if(isset($tutors) && count($tutors) > 0)
                @foreach($tutors as $tutor)
                    <x-tutor-card :tutor="$tutor" />
                @endforeach
            @else
                <p style="color: #94a3b8; font-size: 14px; grid-column: 1 / -1;">
                    {{ request('search') ? 'ไม่พบติวเตอร์ที่ตรงกับคำค้นหา' : 'ยังไม่มีข้อมูลติวเตอร์ในระบบ' }}
                </p>
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
