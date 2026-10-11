<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายวิชาทั้งหมด - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('student.tutors.index') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('subjects.index') }}" class="menu-btn {{ request()->routeIs('subjects.*') ? 'active' : '' }}">รายวิชาทั้งหมด</a>
            <a href="{{ route('student.tutors.index') }}" class="menu-btn {{ request()->routeIs('student.tutors.*') ? 'active' : '' }}">ติวเตอร์ทั้งหมด</a>
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
                                <p style="margin: 0; font-size: 12px; color: #64748b;">นักเรียน</p>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มเข้าหน้าโปรไฟล์เต็มจุดเดียวชัดๆ -->
                    <a href="{{ route('student.profile') }}" style="display: block; text-align: center; text-decoration: none; background-color: #fdeee8; color: #e07a5f; font-weight: 600; padding: 10px; border-radius: 8px; margin-bottom: 16px; transition: background-color 0.2s;">
                        👤 ดูโปรไฟล์ของฉัน
                    </a>

                    <!-- Role Switcher -->
                    <div class="card-section-title" style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">สลับบทบาท</div>
                    <div class="role-grid" style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn active" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 13px;">นักเรียน</h5>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
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