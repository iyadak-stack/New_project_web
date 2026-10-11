<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลักติวเตอร์ - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/tutor-dashboard.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-ranking.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Navbar -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('subjects.index') }}" class="menu-btn {{ request()->routeIs('subjects.*') ? 'active' : '' }}">รายวิชาทั้งหมด</a>
            <a href="{{ route('tutor.search') }}" class="menu-btn {{ request()->routeIs('tutor.search') ? 'active' : '' }}">ติวเตอร์ทั้งหมด</a>
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <a href="{{ route('tutor.favorites') }}" class="menu-btn {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">รายการโปรด</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}">แจ้งเตือน</a>

            <!-- รูปโปรไฟล์วงกลม + Pop-over Card "โปรไฟล์ของฉัน" -->
            <div class="user-profile-wrapper">
                <button type="button" class="user-avatar-btn" id="toggleProfileBtn">
                    @if (Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="user-avatar-img">
                    @else
                        <div class="user-avatar-placeholder">
                            {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                        </div>
                    @endif
                </button>

                <!-- Pop-over Card แบบคลีน ใช้งานง่าย ไม่ซับซ้อน -->
                <div class="profile-card-dropdown" id="profileDropdown">
                    <div class="card-head" style="padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            @if (Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                            @else
                                <div class="user-avatar-placeholder" style="width:44px; height:44px; border-radius:50%; font-size: 16px;">
                                    {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">คุณ{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                                <p style="margin: 0; font-size: 12px; color: #e07a5f; font-weight: 500;">ติวเตอร์</p>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มเข้าหน้าโปรไฟล์เต็มจุดเดียวชัดๆ -->
                    <a href="{{ route('tutor.profile') }}" style="display: block; text-align: center; text-decoration: none; background-color: #fdeee8; color: #e07a5f; font-weight: 600; padding: 10px; border-radius: 8px; margin-bottom: 16px; transition: background-color 0.2s;">
                        👤 ดูโปรไฟล์ของฉัน
                    </a>

                    <!-- Role Switcher -->
                    <div class="card-section-title" style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">สลับบทบาท</div>
                    <div class="role-grid" style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn {{ Auth::user()->current_role !== 'tutor' ? 'active' : '' }}" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 13px;">นักเรียน</h5>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn {{ Auth::user()->current_role === 'tutor' ? 'active' : '' }}" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 13px;">ติวเตอร์</h5>
                            </button>
                        </form>
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="card-logout-btn" style="width: 100%; padding: 8px; border-radius: 8px; background: #fef2f2; color: #ef4444; border: none; font-size: 13px; font-weight: 500; cursor: pointer;">
                            ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main class="td-page">
        {{-- ถ้ามี content จากหน้าย่อย (เช่น profile.blade.php หรือ ranking.blade.php) ให้โชว์เฉพาะหน้าย่อย --}}
        @hasSection('content')
            <div class="td-card" style="margin-bottom: 24px; border-radius: 16px; background: #ffffff;">
                @yield('content')
            </div>
        @else
            {{-- ถ้าไม่มี content ให้โชว์หน้า Dashboard หลัก --}}
            <section class="td-welcome">
                <h1>ยินดีต้อนรับคุณ <b style="color:#e07a5f;">{{ Auth::check() ? Auth::user()->first_name : '' }}</b> สู่ <b>PeerTutor</b></h1>
                <p>จัดการคำขอจอง ตารางสอน จากหน้านี้ พร้อมตัดสินใจตอบรับหรือปฏิเสธคำขอจองได้ทันที</p>
            </section>

            <div class="td-layout">
                <!-- ฝั่งซ้าย -->
                <div class="td-stack">
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
                                        <th>นักเรียน</th>
                                        <th>วันเวลา</th>
                                        <th>วิชา</th>
                                        <th>สถานะ</th>
                                        <th>การตัดสินใจ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="td-main">น้องฟ้า</span><span class="td-sub">ม.4</span></td>
                                        <td><span class="td-main">วันจันทร์</span><span class="td-sub strong">16:00–17:30</span></td>
                                        <td><span class="td-main">คณิตศาสตร์</span><span class="td-sub">ออนไลน์</span></td>
                                        <td><span class="td-status">รอตรวจสอบ</span></td>
                                        <td>
                                            <div class="td-cell-actions">
                                                <button type="button" class="td-btn">ตอบรับ</button>
                                                <button type="button" class="td-btn outline">ปฏิเสธ</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="td-main">น้องมิว</span><span class="td-sub">ม.5</span></td>
                                        <td><span class="td-main">วันพุธ</span><span class="td-sub strong">18:00–19:30</span></td>
                                        <td><span class="td-main">ฟิสิกส์</span><span class="td-sub">ออนไลน์</span></td>
                                        <td><span class="td-status">รอตรวจสอบ</span></td>
                                        <td>
                                            <div class="td-cell-actions">
                                                <button type="button" class="td-btn">ตอบรับ</button>
                                                <button type="button" class="td-btn outline">ปฏิเสธ</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="td-main">น้องปัน</span><span class="td-sub">ม.6</span></td>
                                        <td><span class="td-main">วันศุกร์</span><span class="td-sub strong">17:00–18:30</span></td>
                                        <td><span class="td-main">คอมพิวเตอร์</span><span class="td-sub">ออนไลน์</span></td>
                                        <td><span class="td-status">รอตรวจสอบ</span></td>
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

                <!-- ฝั่งขวา -->
                <div class="td-stack">
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
                            <p>น้องฟ้า • วันจันทร์ <b>16:00–17:30</b></p>
                        </div>

                        <div class="td-lesson">
                            <div class="td-lesson-top">
                                <strong>ฟิสิกส์</strong>
                                <span class="td-status">ยืนยันแล้ว</span>
                            </div>
                            <p>น้องมิว • วันพุธ <b>18:00–19:30</b></p>
                        </div>

                        <div class="td-lesson">
                            <div class="td-lesson-top">
                                <strong>คอมพิวเตอร์</strong>
                                <span class="td-status muted">รอการยืนยัน</span>
                            </div>
                            <p>น้องปัน • วันศุกร์ <b>17:00–18:30</b></p>
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

    <!-- เปิด/ปิดแผงโปรไฟล์ -->
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