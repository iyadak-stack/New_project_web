<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การแจ้งเตือน - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <!-- ลิงก์ไฟล์ CSS แจ้งเตือนแยก -->
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Header / Navbar ด้านบน -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn">หน้าแรก</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn">จัดอันดับติวเตอร์</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn active">แจ้งเตือน</a>

            <!-- รูปโปรไฟล์วงกลม + Pop-over Card "โปรไฟล์ของฉัน" -->
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

                <!-- Pop-over Card -->
                <div class="profile-card-dropdown" id="profileDropdown">
                    @if (Auth::check())
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
                                    <p>{{ Auth::user()->current_role === 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }}</p>
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

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
                            @csrf
                            <button type="submit" class="card-logout-btn">ออกจากระบบ</button>
                        </form>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Notification Area -->
    <main class="notif-container">

        <!-- ปุ่มย้อนกลับ -->
        <div class="notif-back-box">
             <a href="{{ route('home') }}" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
                    ← กลับหน้าหลัก
            </a>
        </div>

        <div class="notif-header">
            <h1 class="notif-title">
                🔔 การแจ้งเตือน
            </h1>
            <button class="btn-mark-all">อ่านแล้วทั้งหมด</button>
        </div>

        <div class="notif-list">
            
            <!-- ตัวอย่าง 1: ยืนยันการนัดหมาย (อ่านแล้ว) -->
            <div class="notif-card">
                <div class="notif-icon icon-confirmed">✓</div>
                <div class="notif-body">
                    <p class="notif-message">
                        <strong>พี่SAKPHON</strong> ได้ยืนยันคำขอจองเรียนวิชา <strong>แคลคูลัส 1</strong> เรียบร้อยแล้ว
                    </p>
                    <span class="notif-time">10 นาทีที่แล้ว</span>
                </div>
                <div class="notif-actions">
                    <a href="{{ route('appointments.index') }}" class="btn-notif-action">ดูตารางเรียน</a>
                    <button class="btn-delete" title="ลบการแจ้งเตือน">&times;</button>
                </div>
            </div>

            <!-- ตัวอย่าง 2: คำขอจองใหม่ (ยังไม่อ่าน) -->
            <div class="notif-card unread">
                <div class="notif-icon icon-pending">📅</div>
                <div class="notif-body">
                    <p class="notif-message">
                        คุณได้รับคำขอจองเรียนใหม่จาก <strong>คุณtub tim</strong> ในวิชา <strong>ฟิสิกส์ทั่วไป</strong>
                    </p>
                    <span class="notif-time">2 ชั่วโมงที่แล้ว</span>
                </div>
                <div class="notif-actions">
                    <a href="{{ route('appointments.index') }}" class="btn-notif-action">ตอบรับคำขอ</a>
                    <button class="btn-delete" title="ลบการแจ้งเตือน">&times;</button>
                </div>
            </div>

            <!-- ตัวอย่าง 3: แจ้งเตือนระบบ -->
            <div class="notif-card">
                <div class="notif-icon icon-system">📢</div>
                <div class="notif-body">
                    <p class="notif-message">
                        ยินดีด้วย! บัญชีติวเตอร์ของคุณได้รับการอนุมัติเรียบร้อยแล้ว
                    </p>
                    <span class="notif-time">เมื่อวานนี้</span>
                </div>
                <div class="notif-actions">
                    <button class="btn-delete" title="ลบการแจ้งเตือน">&times;</button>
                </div>
            </div>

            <!-- Loop ดึงการแจ้งเตือนจริงจาก Database (ถ้ามี) -->
            @if(isset($notifications) && $notifications->count() > 0)
                @foreach($notifications as $notification)
                    <div class="notif-card {{ $notification->read_at ? '' : 'unread' }}">
                        <div class="notif-icon icon-system">🔔</div>
                        <div class="notif-body">
                            <p class="notif-message">{{ $notification->data['message'] ?? $notification->message }}</p>
                            <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="notif-actions">
                            <button class="btn-delete">&times;</button>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>

    </main>

    <!-- JS สำหรับ Pop-over Card Navbar -->
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