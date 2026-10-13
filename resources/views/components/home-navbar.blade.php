<x-logout-confirm />
@if (auth()->guest())
    <x-guest-navbar />
@elseif (auth()->user()->current_role === 'tutor')
    {{-- ใช้ navbar ติวเตอร์ชุดเดียวกับหน้าอื่น โดยไม่แก้หน้าจัดเวลา --}}
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <x-navbar />
@else
<header class="navbar">
    <a href="{{ route('home') }}" class="logo">PeerTutor</a>
    <nav class="menu">
        <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">
            หน้าแรก
        </a>
        @if(auth()->check() && auth()->user()->current_role === 'tutor')
            @if(auth()->user()->tutorProfile?->approval_status === 'approved')
                <a class="menu-btn" href="{{ route('tutor.profile.edit') }}">จัดการโปรไฟล์และวิชา</a>
                <a class="menu-btn" href="{{ route('availabilities.index') }}">จัดการวันเวลาว่าง</a>
                <a class="menu-btn" href="{{ route('appointments.index') }}">คำขอและนัดหมาย</a>
            @else
                <a class="menu-btn" href="{{ route('tutor.application.index') }}">สถานะใบสมัคร</a>
            @endif
        @else
            @auth
                <a href="{{ route('subjects.index') }}" class="menu-btn">รายวิชาทั้งหมด</a>
                <a href="{{ route('student.tutors.index') }}" class="menu-btn">ติวเตอร์ทั้งหมด</a>
            @endauth
            <a href="{{ route('tutor.search') }}" class="menu-btn">ค้นหาติวเตอร์</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn">จัดอันดับติวเตอร์</a>
            <x-nav-user-links />
        @endif
        <x-notification-nav-link />
        @auth
            {{-- เมนูที่ปกป้องเพิ่ม: ติวเตอร์ที่อนุมัติแล้วมีเมนูจัดการวันเวลาว่างด้านบนอยู่แล้ว --}}
            @unless(auth()->user()->current_role === 'tutor' && auth()->user()->tutorProfile?->approval_status === 'approved')
                <a class="menu-btn {{ request()->routeIs('availabilities.*') ? 'active' : '' }}" href="{{ route('availabilities.index') }}">เวลาว่างของฉัน</a>
            @endunless
            <a class="menu-btn {{ request()->routeIs('schedule.check*', 'schedule.booking') ? 'active' : '' }}" href="{{ route('schedule.check') }}">เช็กเวลาตรงกัน</a>
            <a class="menu-btn {{ request()->routeIs('schedule.history') ? 'active' : '' }}" href="{{ route('schedule.history') }}">ประวัติการเรียนและการสอน</a>
        @endauth
        @auth
            <!-- รูปโปรไฟล์วงกลม + Pop-over Card "โปรไฟล์ของฉัน" --><div class="user-profile-wrapper">
                <button type="button" class="user-avatar-btn" id="sharedProfileToggle" aria-expanded="false" aria-controls="sharedProfileDropdown" aria-label="เปิดเมนูบัญชี">
                    @if (Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="user-avatar-img">
                    @else
                        <div class="user-avatar-placeholder">
                            {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                        </div>
                    @endif
                </button>
                <!-- Pop-over Card --><div class="profile-card-dropdown" id="sharedProfileDropdown">
                    <div class="card-head">
                        <div>
                            <h3>โปรไฟล์ของฉัน</h3>
                            <p>ดูข้อมูลส่วนตัว และตารางนัดหมายของคุณ</p>
                        </div>
                        <a href="{{ auth()->user()->current_role === 'tutor' ? route('tutor.profile.edit') : route('student.profile.edit') }}" class="btn-settings-icon" title="ตั้งค่าโปรไฟล์">
                            ⚙
                        </a>
                    </div>
                    <!-- User Info --><div class="user-info-box">
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
                                <p>{{ ['tutor' => 'ติวเตอร์', 'student' => 'นักเรียน', 'admin' => 'ผู้ดูแลระบบ'][auth()->user()->current_role] ?? 'ผู้ใช้' }}</p>
                            </div>
                        </div>
                        <a href="{{ auth()->user()->current_role === 'tutor' ? route('tutor.profile.edit') : route('student.profile.edit') }}" class="btn-edit-profile">
                            แก้ไขโปรไฟล์
                        </a>
                    </div>
                    @if(auth()->user()->tutorProfile)
                        <a class="btn-edit-profile" href="{{ route('tutor.application.index') }}">สถานะใบสมัครติวเตอร์</a>
                    @endif
                    <!-- Role Switcher --><div class="card-section-title">
                        สลับบทบาท
                    </div>
                    <div class="role-grid">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn {{ auth()->user()->current_role === 'student' ? 'active' : '' }}">
                                <h5>นักเรียน</h5>
                                <p>ค้นหาติวเตอร์ และจองเรียนตามตารางที่สะดวก</p>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn {{ auth()->user()->current_role === 'tutor' ? 'active' : '' }}">
                                <h5>ติวเตอร์</h5>
                                <p>เปิดรับเรียนและจัดการนัดหมายของนักเรียน</p>
                            </button>
                        </form>
                    </div>
                    <!-- Logout --><form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="card-logout-btn">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="menu-btn login">เข้าสู่ระบบ</a>
        @endauth
    </nav>
</header>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // แต่ละ component ใช้ชื่อปุ่มต่างกัน แต่เปิดแผงโปรไฟล์แบบเดิม
        const profileButton = document.getElementById('sharedProfileToggle') || document.getElementById('toggleProfileBtn');
        const profileMenu = document.getElementById('sharedProfileDropdown') || document.getElementById('profileDropdown');

        // ผู้ที่ยังไม่เข้าสู่ระบบจะไม่มีปุ่มโปรไฟล์
        if (!profileButton || !profileMenu) {
            return;
        }

        function closeProfileMenu() {
            profileMenu.classList.remove('show');
            profileButton.setAttribute('aria-expanded', 'false');
        }

        profileButton.addEventListener('click', function (event) {
            event.stopPropagation();
            profileMenu.classList.toggle('show');
            profileButton.setAttribute('aria-expanded', profileMenu.classList.contains('show'));
        });

        // คลิกด้านนอกหรือกด Escape เพื่อปิดเมนู
        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target)) {
                closeProfileMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileMenu();
            }
        });
    });
</script>
