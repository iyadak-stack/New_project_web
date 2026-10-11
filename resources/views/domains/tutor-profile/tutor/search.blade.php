<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติวเตอร์ทั้งหมด / ผลการค้นหา - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Header / Navbar ด้านบน 5 แท็บมาตรฐาน -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search', $search ?? '') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('subjects.index') }}" class="menu-btn {{ request()->routeIs('subjects.*') ? 'active' : '' }}">รายวิชาทั้งหมด</a>
            <a href="{{ route('tutor.search') }}" class="menu-btn {{ request()->routeIs('tutor.search') ? 'active' : '' }}">ติวเตอร์ทั้งหมด</a>
            <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">ตารางนัดหมาย</a>
            <a href="{{ route('tutor.favorites') }}" class="menu-btn {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">รายการโปรด</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}">แจ้งเตือน</a>

            <!-- Pop-over Profile Card -->
            <div class="user-profile-wrapper">
                <button type="button" class="user-avatar-btn" id="toggleProfileBtn">
                    @if (Auth::check() && Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" class="user-avatar-img">
                    @else
                        <div class="user-avatar-placeholder">
                            {{ Auth::check() ? mb_substr(Auth::user()->first_name ?? Auth::user()->email, 0, 1) : 'G' }}
                        </div>
                    @endif
                </button>

                <div class="profile-card-dropdown" id="profileDropdown">
                    @if (Auth::check())
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
                                    <p style="margin: 0; font-size: 12px; color: #64748b;">{{ Auth::user()->current_role === 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }}</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ Auth::user()->current_role === 'tutor' ? route('tutor.profile') : route('student.profile') }}" style="display: block; text-align: center; text-decoration: none; background-color: #fdeee8; color: #e07a5f; font-weight: 600; padding: 10px; border-radius: 8px; margin-bottom: 16px;">
                            👤 ดูโปรไฟล์ของฉัน
                        </a>

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

                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="card-logout-btn" style="width: 100%; padding: 8px; border-radius: 8px; background: #fef2f2; color: #ef4444; border: none; font-size: 13px; font-weight: 500; cursor: pointer;">
                                ออกจากระบบ
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <main style="max-width: 1200px; margin: 32px auto; padding: 0 20px;">
        
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                🔍 {{ request('search') ? 'ผลการค้นหาติวเตอร์' : 'ติวเตอร์ทั้งหมดในระบบ' }}
            </h1>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                @if(request('search'))
                    ผลการค้นหาสำหรับคำว่า: <b style="color: #e07a5f;">"{{ request('search') }}"</b> (พบ {{ $tutors->count() }} รายการ)
                @else
                    ค้นหาและเลือกดูโปรไฟล์ติวเตอร์ที่ตรงกับความต้องการของคุณ (ทั้งหมด {{ $tutors->count() }} ท่าน)
                @endif
            </p>
        </div>

        @if ($tutors->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 24px;">
                @foreach ($tutors as $tutor)
                    @php
                        $tutorUser = $tutor->user;
                        $rating = number_format($tutor->average_rating ?? 0.0, 1);
                        $reviewCount = $tutor->reviews_count ?? ($tutor->reviews ? $tutor->reviews->count() : 0);
                    @endphp

                    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #fce7f3; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 12px;">
                        
                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 72px; height: 72px; border-radius: 16px; overflow: hidden; background: #fff0f3; flex-shrink: 0;">
                                @if ($tutorUser?->profile_picture)
                                    <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" alt="Tutor" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; font-size: 26px; font-weight: 700; background: #fca5a5; color: #fff; display: flex; align-items: center; justify-content: center;">
                                        {{ mb_substr($tutorUser?->first_name ?? 'P', 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">
                                    {{ $tutorUser?->first_name }} {{ $tutorUser?->last_name }}
                                </h3>

                                <p style="font-size: 14px; color: #64748b; margin: 0;">
                                    สอนพิเศษ {{ $tutor->experience_years ?? 0 }} ชั่วโมง
                                </p>

                                <div style="font-size: 14px; color: #e07a5f; font-weight: 600; margin-top: 2px;">
                                    ⭐ {{ $rating }} <span style="color: #64748b; font-weight: 400;">จาก {{ $reviewCount }} รีวิว</span>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 14px; color: #64748b;">
                            @if($tutor->subjects && $tutor->subjects->count() > 0)
                                {{ $tutor->subjects->pluck('subject_name')->implode(', ') }}
                            @else
                                ไม่มีรายละเอียดวิชา
                            @endif
                        </div>

                        <div>
                            <a href="{{ route('tutor.show', $tutor->tutor_id) }}" style="display: inline-block; background: #e07a5f; color: #ffffff; padding: 8px 24px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                ดูโปรไฟล์ / จองเรียน
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div style="border: 1px dashed #cbd5e1; border-radius: 16px; padding: 60px 20px; text-align: center; background: #ffffff; margin-bottom: 24px;">
                <div style="font-size: 40px; margin-bottom: 12px;">🔍</div>
                <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">ไม่พบผลการค้นหาติวเตอร์</h2>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 24px;">ลองค้นหาด้วยคำค้นอื่น เช่น ชื่อวิชา หรือชื่อติวเตอร์</p>
            </div>
        @endif

        <div style="margin-top: 16px;">
            <button type="button" onclick="window.history.back()" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer;">
                ← ย้อนกลับ
            </button>
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