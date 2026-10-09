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
            @php
                $prevUrl = url()->previous();
                $isFromRanking = request()->routeIs('tutor.ranking') || (request()->routeIs('tutor.show') && str_contains($prevUrl, 'ranking'));
                $isFromHome = (request()->routeIs('home') || request()->routeIs('tutor.dashboard')) && !$isFromRanking;
            @endphp
            {{-- 🟢 เช็ก active ตาม Route จริง ไม่ฮาร์ดโค้ด active ค้างไว้ --}}
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') || request()->routeIs('tutor.dashboard') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn {{ request()->routeIs('tutor.ranking') ? 'active' : '' }}">จัดอันดับติวเตอร์</a>
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

                <!-- Pop-over Card -->
                <div class="profile-card-dropdown" id="profileDropdown">
                    <div class="card-head">
                        <div>
                            <h3>โปรไฟล์ของฉัน</h3>
                            <p>ดูข้อมูลส่วนตัว และตารางนัดหมายของคุณ</p>
                        </div>
                        <a href="{{ Auth::user()->current_role === 'tutor' ? route('tutor.profile.edit') : route('student.profile.edit') }}" class="btn-settings-icon" title="ตั้งค่าโปรไฟล์">⚙</a>
                    </div>

                    <!-- User Info -->
                    <div class="user-info-box">
                        <div class="user-info-inner">
                            @if (Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="info-avatar">
                            @else
                                <div class="user-avatar-placeholder" style="width:48px; height:48px; border-radius:12px;">
                                    {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                                </div>
                            @endif

                            <div class="info-details">
                                <h4>คุณ{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                                <p>{{ Auth::user()->current_role === 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }} - พร้อมสอนวิชาเพิ่มเติม</p>
                            </div>
                        </div>

                        @if(Auth::user()->current_role === 'tutor')
                            <a href="{{ route('tutor.profile.edit') }}" class="btn-edit-profile">แก้ไขโปรไฟล์</a>
                        @else
                            <a href="{{ route('student.profile.edit') }}" class="btn-edit-profile">แก้ไขโปรไฟล์</a>
                        @endif
                    </div>

                    <!-- Role Switcher -->
                    <div class="card-section-title">สลับบทบาท</div>
                    <div class="role-grid">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn {{ Auth::user()->current_role !== 'tutor' ? 'active' : '' }}">
                                <h5>นักเรียน</h5>
                                <p>ค้นหาติวเตอร์ และจองเรียนตามตารางที่สะดวก</p>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn {{ Auth::user()->current_role === 'tutor' ? 'active' : '' }}">
                                <h5>ติวเตอร์</h5>
                                <p>เปิดรับเรียนและจัดการนัดหมายของนักเรียน</p>
                            </button>
                        </form>
                    </div>

                    <!-- ช่องทางติดต่อในการสอน -->
                    @php
                        $userContact = Auth::user()->contact 
                            ?? \App\Domains\Auth\Models\Contact::where('Users_user_id', Auth::id())->first();
                    @endphp

                    <div class="contact-channels-box" style="margin-top: 16px; background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #f3f4f6;">
                        <h4 style="font-size: 15px; font-weight: 600; color: #1f2937; margin-bottom: 12px;">📞 ช่องทางติดต่อในการสอน</h4>
    
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #4b5563;">
                            <div><strong>Line ID:</strong> {{ $userContact?->line_id ?: '-' }}</div>
                            <div><strong>Discord:</strong> {{ $userContact?->discord_id ?: '-' }}</div>
                            <div>
                                <strong>Zoom:</strong> 
                                @if(!empty($userContact?->zoom_link))
                                    <a href="{{ $userContact->zoom_link }}" target="_blank" style="color: #2563eb; text-decoration: underline; word-break: break-all;">
                                        เปิดลิงก์ Zoom
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="card-logout-btn">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main class="td-page">
        {{-- 🟢 ถ้ามี content จากหน้าย่อย (เช่น ranking.blade.php) ให้โชว์เฉพาะหน้าย่อย --}}
        @hasSection('content')
            <div class="td-card" style="margin-bottom: 24px;">
                @yield('content')
            </div>
        @else
            {{-- 🟢 ถ้าไม่มี content ให้โชว์หน้า Dashboard หลัก --}}
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