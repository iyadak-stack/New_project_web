<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>หน้าหลักติวเตอร์ - PeerTutor</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        {{-- ปกป้องแก้ให้: path เดิม css/tutor-dashboard.css ต้องเป็น CSS/tutor-dashboard.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/tutor-dashboard.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/tutor-navbar.css ต้องเป็น CSS/tutor-navbar.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/tutor-ranking.css ต้องเป็น CSS/tutor-ranking.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/tutor-ranking.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    </head>
    <body>
        <!-- Navbar --><x-home-navbar />
        <main class="td-page">
            {{-- 🟢 ถ้ามี content จากหน้าย่อย (เช่น ranking.blade.php) ให้โชว์เฉพาะหน้าย่อย --}}
        @hasSection('content')
            <div class="td-card" style="margin-bottom: 24px;">
                @yield('content')
            </div>
        @else
            {{-- 🟢 ถ้าไม่มี content ให้โชว์หน้า Dashboard หลัก --}}
            <section class="td-welcome">
                <h1>
                    ยินดีต้อนรับคุณ
                    <b style="color:#e07a5f;">
                        {{ Auth::check() ? Auth::user()->first_name : '' }}
                    </b>
                    สู่
                    <b>
                        PeerTutor
                    </b>
                </h1>
                <p>จัดการคำขอจอง ตารางสอน จากหน้านี้ พร้อมตัดสินใจตอบรับหรือปฏิเสธคำขอจองได้ทันที</p>
            </section>
            <div class="td-layout">
                <!-- ฝั่งซ้าย --><div class="td-stack">
                    <section class="td-card">
                        <div class="td-card-head">
                            <div>
                                <h2>คำขอจอง</h2>
                                <p>มี 3 คำขอใหม่รอตรวจสอบ</p>
                            </div>
                            <span class="td-pill">3 รายการ</span>
                        </div>
                        <div class="td-stats">
                            <div class="td-stat">
                                <small>รอตรวจสอบ</small>
                                <strong>3</strong>
                            </div>
                            <div class="td-stat">
                                <small>ตอบรับแล้ว</small>
                                <strong>8</strong>
                            </div>
                            <div class="td-stat">
                                <small>ปฏิเสธ</small>
                                <strong>2</strong>
                            </div>
                        </div>
                    </section>
                    <section class="td-card">
                        <div class="td-card-head">
                            <div>
                                <h2>ตารางคำขอจอง</h2>
                                <p>ตรวจสอบรายละเอียดนักเรียน วันเวลา วิชา และสถานะ เพื่อตัดสินใจตอบรับหรือปฏิเสธ</p>
                            </div>
                            <span class="td-pill">ทั้งหมด 3 รายการ</span>
                        </div>
                        <div class="td-table-wrap">
                            <table class="td-table">
                                <thead>
                                    <tr>
                                        <th>
                                            นักเรียน
                                        </th>
                                        <th>
                                            วันเวลา
                                        </th>
                                        <th>
                                            วิชา
                                        </th>
                                        <th>
                                            สถานะ
                                        </th>
                                        <th>
                                            การตัดสินใจ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="td-main">น้องฟ้า</span>
                                            <span class="td-sub">ม.4</span>
                                        </td>
                                        <td>
                                            <span class="td-main">วันจันทร์</span>
                                            <span class="td-sub strong">16:00–17:30</span>
                                        </td>
                                        <td>
                                            <span class="td-main">คณิตศาสตร์</span>
                                            <span class="td-sub">ออนไลน์</span>
                                        </td>
                                        <td>
                                            <span class="td-status">รอตรวจสอบ</span>
                                        </td>
                                        <td>
                                            <div class="td-cell-actions">
                                                <button type="button" class="td-btn">ตอบรับ</button>
                                                <button type="button" class="td-btn outline">ปฏิเสธ</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="td-main">น้องมิว</span>
                                            <span class="td-sub">ม.5</span>
                                        </td>
                                        <td>
                                            <span class="td-main">วันพุธ</span>
                                            <span class="td-sub strong">18:00–19:30</span>
                                        </td>
                                        <td>
                                            <span class="td-main">ฟิสิกส์</span>
                                            <span class="td-sub">ออนไลน์</span>
                                        </td>
                                        <td>
                                            <span class="td-status">รอตรวจสอบ</span>
                                        </td>
                                        <td>
                                            <div class="td-cell-actions">
                                                <button type="button" class="td-btn">ตอบรับ</button>
                                                <button type="button" class="td-btn outline">ปฏิเสธ</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="td-main">น้องปัน</span>
                                            <span class="td-sub">ม.6</span>
                                        </td>
                                        <td>
                                            <span class="td-main">วันศุกร์</span>
                                            <span class="td-sub strong">17:00–18:30</span>
                                        </td>
                                        <td>
                                            <span class="td-main">คอมพิวเตอร์</span>
                                            <span class="td-sub">ออนไลน์</span>
                                        </td>
                                        <td>
                                            <span class="td-status">รอตรวจสอบ</span>
                                        </td>
                                        <td>
                                            <div class="td-cell-actions">
                                                <button type="button" class="td-btn">ตอบรับ</button>
                                                <button type="button" class="td-btn outline">ปฏิเสธ</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <!-- ฝั่งขวา --><div class="td-stack">
                    <section class="td-card">
                        <div class="td-card-head">
                            <div>
                                <h2>ตารางสอนวันนี้</h2>
                                <p>มี 2 คาบ และ 1 คาบรอการยืนยัน</p>
                            </div>
                            <span class="td-pill">2 คาบ</span>
                        </div>
                        <div class="td-lesson">
                            <div class="td-lesson-top">
                                <strong>คณิตศาสตร์</strong>
                                <span class="td-status">ยืนยันแล้ว</span>
                            </div>
                            <p>
                                น้องฟ้า • วันจันทร์
                                <b>
                                    16:00–17:30
                                </b>
                            </p>
                        </div>
                        <div class="td-lesson">
                            <div class="td-lesson-top">
                                <strong>ฟิสิกส์</strong>
                                <span class="td-status">ยืนยันแล้ว</span>
                            </div>
                            <p>
                                น้องมิว • วันพุธ
                                <b>
                                    18:00–19:30
                                </b>
                            </p>
                        </div>
                        <div class="td-lesson">
                            <div class="td-lesson-top">
                                <strong>คอมพิวเตอร์</strong>
                                <span class="td-status muted">รอการยืนยัน</span>
                            </div>
                            <p>
                                น้องปัน • วันศุกร์
                                <b>
                                    17:00–18:30
                                </b>
                            </p>
                        </div>
                        <div class="td-actions">
                            <a href="{{ route('appointments.index') }}" class="td-btn">ดูรายละเอียด</a>
                            <a href="{{ route('notifications.index') }}" class="td-btn outline">แจ้งเตือน</a>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </main>
    <!-- เปิด/ปิดแผงโปรไฟล์ --><script>
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
