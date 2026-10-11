@php
    // เช็กบทบาทจาก Session ก่อน ถ้าไม่มีให้เช็กจากคอลัมน์ current_role ของผู้ใช้ หรือ default เป็น 'student'
    $currentRole = session('active_role', Auth::user()->current_role ?? 'student');
@endphp

@if ($currentRole === 'tutor')
    <!-- ================= NAVBAR ติวเตอร์ ================= -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('subjects.index') }}" class="menu-btn {{ request()->routeIs('subjects.*') ? 'active' : '' }}">รายวิชาทั้งหมด</a>
            <a href="{{ route('tutor.tutors') }}" class="menu-btn {{ request()->routeIs('tutor.tutors') ? 'active' : '' }}">ติวเตอร์ทั้งหมด</a>
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}">แจ้งเตือน</a>

            <!-- Pop-over Card บทบาทติวเตอร์ -->
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
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <a href="{{ route('tutor.favorites') }}" class="menu-btn {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">รายการโปรด</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}">แจ้งเตือน</a>

            <!-- Pop-over Card บทบาทนักเรียน -->
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