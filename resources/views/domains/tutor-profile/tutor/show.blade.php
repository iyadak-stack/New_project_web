<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดติวเตอร์ - PeerTutor</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-detail.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Header / Navbar ด้านบน -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>

        <nav class="menu">
            @php
                $prevUrl = url()->previous();
                $isFromRanking = request()->routeIs('tutor.ranking') || str_contains($prevUrl, 'ranking');
                $isFromHome = !$isFromRanking;
            @endphp

            <a href="{{ route('home') }}" class="menu-btn {{ $isFromHome ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn {{ $isFromRanking ? 'active' : '' }}">จัดอันดับติวเตอร์</a>
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
                                <p>{{ Auth::user()->current_role === 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }} - พร้อมเรียนวิชาเพิ่มเติม</p>
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

    <main class="tutor-detail-container">

        <!-- Breadcrumb & ปุ่มย้อนกลับ -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <a href="{{ route('home') }}" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">
                    ← กลับหน้าหลัก
            </a>
        </div>

        @php
            $tutorUser = $tutorProfile->user;
        @endphp
        
        <!-- Header Badge Profile Card ของติวเตอร์คนนี้ -->
        <div class="tutor-header-card">
            <div class="tutor-header-flex">
                <div class="tutor-avatar">
                    <!-- รูปโปรไฟล์ของติวเตอร์ -->
                    @if ($tutorUser?->profile_picture)
                        <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" alt="รูปโปรไฟล์ติวเตอร์">
                    @else
                        <div class="user-avatar-placeholder" style="width:100%; height:100%; font-size:40px;">
                            {{ mb_substr($tutorUser?->first_name ?? '?', 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="tutor-header-info">
                    <div class="tutor-badges">
                        <span class="badge badge-verified">ยืนยันตัวตนแล้ว</span>
                        <span class="badge badge-mode">{{ ucfirst($tutorProfile->teaching_mode) }}</span>
                    </div>
                    <h1 class="tutor-name">
                        พี่{{ $tutorProfile->user?->first_name }} {{ $tutorProfile->user?->last_name }}
                    </h1>
                    <p class="tutor-headline">
                        ประสบการณ์สอน {{ $tutorProfile->experience_years }} ปี
                    </p>
                    <div class="tutor-stats">
                        <span class="rating">⭐ {{ number_format($tutorProfile->average_rating, 1) }} ({{ $tutorProfile->reviews->count() }} รีวิว)</span>
                    </div>
                </div>
                <div class="tutor-fav-action">
                    @if ($isFavorite)
                        <form action="{{ route('tutor.favorite.destroy', $tutorProfile) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-fav active" title="ลบออกจากรายการโปรด">❤️</button>
                        </form>
                    @else
                        <form action="{{ route('tutor.favorite.store', $tutorProfile) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-fav" title="บันทึกในรายการโปรด">🤍</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Grid Layout (2 Columns) -->
        <div class="tutor-grid-layout">
            
            <!-- Left Column: Details -->
            <div class="tutor-main-content">
                
                <!-- วิชาและระดับชั้นที่สอน -->
                <div class="detail-card">
                    <h2 class="card-title">วิชาและระดับชั้นที่สอน</h2>
                    <div class="subjects-grid">
                        @forelse ($tutorProfile->subjects as $subject)
                            <div class="subject-box">
                                <h3 class="subject-name">{{ $subject->subject_name }}</h3>
                                <p class="subject-grade">
                                    {{ $subject->pivot->grade_level ?? 'ระดับ ม.ต้น / ม.ปลาย / มหาวิทยาลัย' }}
                                </p>
                            </div>
                        @empty
                            <p class="empty-text">ยังไม่ได้ระบุวิชาที่สอน</p>
                        @endforelse
                    </div>
                </div>

                <!-- วันและเวลาที่เปิดสอน -->
                <div class="detail-card">
                    <h2 class="card-title">วันและเวลาที่เปิดสอน</h2>
                    @if ($tutorProfile->appointments->count() > 0)
                        <div class="schedule-list">
                            @foreach ($tutorProfile->appointments as $appointment)
                                <div class="schedule-item">
                                    <div class="schedule-time">
                                        <span>📅 {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('d M Y') }}</span>
                                        <span>⏰ {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_datetime)->format('H:i') }} น.</span>
                                    </div>
                                    <span class="status-badge status-{{ strtolower($appointment->status) }}">
                                        {{ $appointment->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="empty-text">ยังไม่มีตารางเวลาที่เปิดสอนในขณะนี้</p>
                    @endif
                </div>

                <!-- รีวิวจากผู้เรียน -->
                <div class="detail-card">
                    <h2 class="card-title">เสียงจากนักเรียน</h2>
                    <div class="review-score-summary">
                        <div class="score-num">{{ number_format($tutorProfile->average_rating, 1) }}</div>
                        <div class="score-stars">
                            <span>⭐⭐⭐⭐⭐</span>
                            <small>จากทั้งหมด {{ $tutorProfile->reviews->count() }} รีวิว</small>
                        </div>
                    </div>

                    <div class="reviews-list">
                        @forelse ($tutorProfile->reviews as $review)
                            <div class="review-item">
                                <div class="review-top">
                                    <strong>ผู้เรียน</strong>
                                    <span class="review-stars">⭐ {{ $review->rating }}/5</span>
                                </div>
                                <p class="review-comment">{{ $review->Comment }}</p>
                            </div>
                        @empty
                            <p class="empty-text">ยังไม่มีรีวิวสำหรับติวเตอร์ท่านนี้</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- การ์ดเลือกเวลาเรียน -->
            <div class="booking-section-card" style="background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #f3f4f6; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-top: 24px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">เลือกเวลาเรียน</h3>

                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tutor_id" value="{{ $tutorProfile->tutor_id }}">

                    <!-- 1. วิชาที่ต้องการเรียน (ดึงจาก Seeder / DB) -->
                    <div class="mb-3">
                        <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">วิชาที่ต้องการเรียน</label>
                        <select name="subject_id" class="form-select" style="width: 100%; border-radius: 12px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background-color: #fff;" required>
                            <option value="" disabled selected>-- เลือกวิชาที่ต้องการเรียน --</option>
                
                            <!-- ดึงเฉพาะวิชาที่ติวเตอร์คนนี้เปิดสอน (ถ้ามี) หรือดึงวิชาทั้งหมด -->
                            @if(isset($tutorProfile->subjects) && $tutorProfile->subjects->count() > 0)
                                @foreach($tutorProfile->subjects as $sub)
                                    <option value="{{ $sub->subject_id }}">{{ $sub->subject_name }}</option>
                                @endforeach
                            @elseif(isset($allSubjects))
                                @foreach($allSubjects as $sub)
                                    <option value="{{ $sub->subject_id }}">{{ $sub->subject_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- 2. ระดับชั้น -->
                    <div class="mb-3">
                        <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">ระดับชั้น</label>
                        <select name="grade_level" class="form-select" style="width: 100%; border-radius: 12px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background-color: #fff;" required>
                            <option value="ประถมศึกษา">ประถมศึกษา</option>
                            <option value="ม.ต้น (ม.1 - ม.3)" selected>ม.ต้น (ม.1 - ม.3)</option>
                            <option value="ม.ปลาย (ม.4 - ม.6)">ม.ปลาย (ม.4 - ม.6)</option>
                            <option value="มหาวิทยาลัย">มหาวิทยาลัย</option>
                            <option value="บุคคลทั่วไป">บุคคลทั่วไป</option>
                        </select>
                    </div>

                    <!-- 3. รูปแบบการเรียน -->
                    <div class="mb-4">
                        <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">รูปแบบการเรียน</label>
                        <div style="width: 100%; padding: 10px; border: 1px solid #e07a5f; border-radius: 12px; text-align: center; color: #e07a5f; font-weight: 600; font-size: 14px; background: #fffaf8;">
                            {{ ucfirst($tutorProfile->teaching_mode ?? 'ออนไลน์') }}
                        </div>
                        <input type="hidden" name="teaching_mode" value="{{ $tutorProfile->teaching_mode ?? 'online' }}">
                    </div>
                </div>

                <!-- ปุ่มส่งคำขอ -->
                <button type="submit" class="btn-submit-request" style="width: 100%; background: #e07a5f; color: #ffffff; border: none; padding: 12px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    ส่งคำขอจองเรียน
                </button>
            </form>
        </div>
    </main>
    <x-login-modal />
    <x-register-modal />

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