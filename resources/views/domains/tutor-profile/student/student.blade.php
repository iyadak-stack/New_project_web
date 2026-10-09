<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerTutor - หน้านักเรียน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn active">หน้าแรก</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn {{ request()->routeIs('tutor.ranking') ? 'active' : '' }}">จัดอันดับติวเตอร์</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn">แจ้งเตือน</a>

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
                        <a href="{{ route('student.profile.edit') }}" class="btn-settings-icon" title="ตั้งค่าโปรไฟล์">⚙</a>
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
                                <p>นักเรียน - พร้อมเรียนวิชาเพิ่มเติม</p>
                            </div>
                        </div>

                        <a href="{{ route('student.profile.edit') }}" class="btn-edit-profile">แก้ไขโปรไฟล์</a>
                    </div>

                    <!-- Role Switcher -->
                    <div class="card-section-title">สลับบทบาท</div>
                    <div class="role-grid">
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="role-card role-btn active">
                                <h5>นักเรียน</h5>
                                <p>ค้นหาติวเตอร์ และจองเรียนตามตารางที่สะดวก</p>
                            </button>
                        </form>
                        <form action="{{ route('role.switch') }}" method="POST" style="margin: 0; flex:1;">
                            @csrf
                            <input type="hidden" name="role" value="tutor">
                            <button type="submit" class="role-card role-btn">
                                <h5>ติวเตอร์</h5>
                                <p>เปิดรับเรียนและจัดการนัดหมายของนักเรียน</p>
                            </button>
                        </form>
                    </div>

                    <!-- Appointment Table Summary -->
                    <div class="card-section-title">ตารางนัดหมาย</div>
                    @if(isset($displayAppointments) && $displayAppointments->count() > 0)
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>เวลา</th>
                                    <th>รายวิชา</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($displayAppointments as $item)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($item->date ?? $item->appointment_date)->locale('th')->translatedFormat('d ม.ค.') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->time ?? $item->start_time)->format('H:i') }}</td>
                                        <td>{{ $item->subject_name ?? $item->subject->name ?? '-' }}</td>
                                        <td>
                                            @if(($item->status ?? '') == 'confirmed')
                                                <span class="status-badge status-badge-confirmed">ยืนยันแล้ว</span>
                                            @elseif(($item->status ?? '') == 'pending')
                                                <span class="status-badge status-badge-pending">รอการตอบรับ</span>
                                            @else
                                                <span class="status-badge">{{ $item->status ?? 'รอยืนยัน' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(isset($hasMoreAppointments) && $hasMoreAppointments)
                            <div class="more-appointments-wrapper">
                                <a href="{{ route('appointments.index') }}" class="more-appointments-btn">เพิ่มเติม ∨</a>
                            </div>
                        @endif
                    @else
                        <div class="no-appointments-box">
                            ไม่มีรายการนัดหมายในขณะนี้
                        </div>
                    @endif

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="card-logout-btn">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main class="page">
        <!-- Welcome Section (ปรับให้อ่านชื่อผู้ใช้ตรงๆ ไม่ต้องเช็ก Auth::check และระยะห่างตรงกับติวเตอร์) -->
        <section class="welcome-box">
            <h1>ยินดีต้อนรับคุณ <b style="color:#e07a5f;">{{ Auth::user()->first_name }}</b> สู่ <b>PeerTutor</b></h1>
            <p>ค้นหาติวเตอร์ที่เข้าใจเนื้อหาเดียวกัน แล้วเริ่มเรียนได้ทันทีด้วยประสบการณ์ที่ใกล้ตัวมากขึ้น</p>
        </section>

        <!-- Top Tutors -->
        <h2 class="section-title">ติวเตอร์ยอดนิยม</h2>
        <p class="section-sub">สำรวจติวเตอร์ที่มีคะแนนสูงสุด</p>

        <div class="grid">
            @foreach($tutors as $tutor)
                <x-tutor-card :tutor="$tutor" />
            @endforeach
        </div>

        <!-- Top Subjects -->
        <h2 class="section-title">รายวิชายอดนิยม</h2>
        <p class="section-sub">รายวิชาที่นักเรียนค้นหามากที่สุดใน <b>PeerTutor</b></p>

        <div class="grid">
            @foreach($subjects as $subject)
                @php
                    $title = is_array($subject) ? ($subject['title'] ?? '') : ($subject->subject_name ?? $subject->title ?? '');
                    $count = is_array($subject) ? ($subject['count'] ?? 0) : ($subject->tutors_count ?? 0);
                    $description = is_array($subject) ? ($subject['description'] ?? '') : ($subject->description ?? 'รายวิชาคุณภาพเยี่ยม');
                    $tags = is_array($subject) ? ($subject['tags'] ?? []) : ($subject->tags ?? []);
                @endphp
                <div class="card card-subject">
                    <div class="subject-top">
                        <h3>{{ $title }}</h3>
                        <span class="count">{{ $count }}+</span>
                    </div>

                    <p class="desc">{{ $description }}</p>

                    <div class="tags">
                        @if(is_iterable($tags))
                            @foreach($tags as $tag)
                                <span class="tag">{{ is_array($tag) ? ($tag['name'] ?? $tag) : $tag }}</span>
                            @endforeach
                        @endif
                    </div>

                    <a href="{{ route('tutor.search', ['search' => $title]) }}" class="btn">ดูรายวิชา</a>
                </div>
            @endforeach
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