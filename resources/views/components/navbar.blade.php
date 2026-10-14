<x-logout-confirm />
@php
    // อ่านบทบาทจริงจากบัญชี ให้ตรงกับหน้าแรกและการตรวจสิทธิ์ใน Controller
    $currentRole = Auth::user()?->current_role ?? 'student';
@endphp

@if (Auth::guest())
    <x-guest-navbar />
@elseif (Auth::user()->role === 'admin' || Auth::user()->current_role === 'admin')
    {{-- แอดมินมีทางเข้ากล่องรับรายงาน โดยตรวจบทบาทซ้ำใน Controller --}}
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>
        <nav class="menu" aria-label="เมนูแอดมิน">
            <a href="{{ route('admin.dashboard') }}" class="menu-btn">หน้าจัดการแอดมิน</a>
            <a href="{{ route('admin.reports.index') }}" class="menu-btn {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">รายงานจากผู้ใช้</a>
            <x-notification-bell />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="menu-btn">ออกจากระบบ</button>
            </form>
        </nav>
    </header>
@elseif ($currentRole === 'tutor')
    <!-- ================= NAVBAR ติวเตอร์ ================= -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('tutor.subjects.index') }}" class="menu-btn {{ request()->routeIs('tutor.subjects.*') ? 'active' : '' }}">วิชาของฉัน</a>
            {{-- จัดเวลามีหน้าและสถานะ active ของตัวเอง --}}
            <a href="{{ route('availabilities.index') }}" class="menu-btn {{ request()->routeIs('availabilities.*') ? 'active' : '' }}">จัดเวลา</a>
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <x-notification-bell />

            <!-- Pop-over Card บทบาทติวเตอร์ -->
            <div class="user-profile-wrapper">
                <button type="button" class="user-avatar-btn" id="toggleProfileBtn" aria-haspopup="true" aria-expanded="false">
                    @if (Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="user-avatar-img">
                    @else
                        <div class="user-avatar-placeholder">
                            {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                        </div>
                    @endif
                </button>

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

                    <a href="{{ route('tutor.profile') }}" style="display: block; text-align: center; text-decoration: none; background-color: #fdeee8; color: #e07a5f; font-weight: 600; padding: 10px; border-radius: 8px; margin-bottom: 16px;">
                        👤 ดูโปรไฟล์ของฉัน
                    </a>

                    <!-- Role Switcher -->
                    <div class="card-section-title" style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">สลับบทบาท</div>
                    <div class="role-grid" style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 13px;">นักเรียน</h5>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn active" style="width: 100%; text-align: left; padding: 8px 10px; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 13px;">ติวเตอร์</h5>
                                {{-- คำขอยังรอตรวจ แสดงสถานะใต้ชื่อบทบาท --}}
                                @if (Auth::user()?->tutorProfile()->value('approval_status') === 'pending')
                                    <span style="display: block; margin-top: 4px; font-size: 11px; color: #a16207;">กำลังตรวจสอบ</span>
                                @endif
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

@else
    <!-- ================= NAVBAR นักเรียน ================= -->
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
            {{-- นักเรียนตั้งเวลาว่างผ่านหน้าเดิมของระบบเพื่อน --}}
            <a href="{{ route('availabilities.index') }}" class="menu-btn {{ request()->routeIs('availabilities.*') ? 'active' : '' }}">จัดเวลา</a>
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <a href="{{ route('tutor.favorites') }}" class="menu-btn {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">รายการโปรด</a>
            <x-notification-bell />

            <!-- Pop-over Card บทบาทนักเรียน -->
            <div class="user-profile-wrapper">
                <button type="button" class="user-avatar-btn" id="toggleProfileBtn" aria-haspopup="true" aria-expanded="false">
                    @if (Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="user-avatar-img">
                    @else
                        <div class="user-avatar-placeholder">
                            {{ mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) }}
                        </div>
                    @endif
                </button>

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

                    <a href="{{ route('student.profile') }}" style="display: block; text-align: center; text-decoration: none; background-color: #fdeee8; color: #e07a5f; font-weight: 600; padding: 10px; border-radius: 8px; margin-bottom: 16px;">
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
                                {{-- คำขอยังรอตรวจ แสดงสถานะใต้ชื่อบทบาท --}}
                                @if (Auth::user()?->tutorProfile()->value('approval_status') === 'pending')
                                    <span style="display: block; margin-top: 4px; font-size: 11px; color: #a16207;">กำลังตรวจสอบ</span>
                                @endif
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
@endif

@auth
<script>
    // กดปุ่มย้อนกลับอาจได้ HTML เก่าจากแคช ให้โหลดใหม่เพื่ออ่านบทบาทล่าสุด
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // ควบคุมเมนูโปรไฟล์ที่เดียว (กันสคริปต์ซ้ำของแต่ละหน้า)
    // ใช้ capture ที่ document เพื่อให้ทำงานก่อน listener ของหน้าอื่นที่ผูกกับปุ่มเดียวกัน
    // แล้วหยุดการส่งต่อ ไม่ให้ถูกสลับซ้ำจนเมนูเปิดแล้วปิดทันที
    (function () {
        if (window.__ptProfileMenuReady) return;
        window.__ptProfileMenuReady = true;

        function parts() {
            return {
                btn: document.getElementById('toggleProfileBtn'),
                menu: document.getElementById('profileDropdown')
            };
        }

        function setOpen(open) {
            var p = parts();
            if (!p.btn || !p.menu) return;
            p.menu.classList.toggle('show', open);
            p.menu.classList.toggle('active', open);
            // ล้าง inline display ที่สคริปต์เก่าของบางหน้าอาจตั้งไว้ ให้ CSS คุมเอง
            p.menu.style.display = '';
            p.btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        document.addEventListener('click', function (e) {
            var p = parts();
            if (!p.btn || !p.menu) return;

            if (p.btn.contains(e.target)) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                setOpen(!p.menu.classList.contains('show'));
                return;
            }

            // คลิกนอกเมนูให้ปิด
            if (!p.menu.contains(e.target)) {
                setOpen(false);
            }
        }, true);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
    })();
</script>
@endauth
