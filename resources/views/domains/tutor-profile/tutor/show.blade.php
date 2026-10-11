<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ติวเตอร์ {{ $tutorProfile->user->first_name ?? '' }} - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-detail.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Header / Navbar ด้านบน 5 แท็บมาตรฐาน -->
    <header class="pt-navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="" placeholder="ค้นหาวิชา ติวเตอร์">
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

    <!-- Main Container -->
    <div class="tutor-detail-container">

        {{-- ข้อความแจ้งผลหลังกดรายการโปรด / จองเรียน --}}
        @if (session('success'))
            <div style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 10px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header Card -->
        <div class="tutor-header-card">
            <div class="tutor-header-flex">
                <div class="tutor-avatar">
                    @if($tutorProfile->user && $tutorProfile->user->profile_picture)
                        <img src="{{ asset('storage/' . $tutorProfile->user->profile_picture) }}" alt="Tutor Avatar">
                    @else
                        <div class="avatar-placeholder">
                            {{ mb_substr($tutorProfile->user->first_name ?? 'T', 0, 1) }}
                        </div>
                    @endif
                </div>

                <div class="tutor-header-info">
                    <div class="tutor-badges">
                        <span class="badge badge-verified">✓ ยืนยันตัวตนแล้ว</span>
                        <span class="badge badge-mode">
                            {{ $tutorProfile->teaching_mode === 'onsite' ? 'สอนตัวต่อตัว (Onsite)' : ($tutorProfile->teaching_mode === 'both' ? 'ออนไลน์ & Onsite' : 'ออนไลน์ (Online)') }}
                        </span>
                    </div>

                    <h1 class="tutor-name">
                        พี่{{ $tutorProfile->user->first_name ?? '' }} {{ $tutorProfile->user->last_name ?? '' }}
                    </h1>

                    <p class="tutor-headline">
                        ประสบการณ์สอน {{ $tutorProfile->experience_years ?? 0 }} ปี
                    </p>

                    <div style="font-size: 14px; color: #e07a5f; font-weight: 600;">
                        ⭐ {{ number_format($tutorProfile->average_rating ?? 5.0, 1) }}
                        <span style="color: #94a3b8; font-weight: 400;">({{ $tutorProfile->reviews_count ?? 0 }} รีวิว)</span>
                    </div>
                </div>

                {{-- ปุ่มรายการโปรด: กดครั้งแรก = เพิ่ม, กดอีกครั้ง = เอาออก --}}
                @if (!empty($isFavorite))
                    <form action="{{ route('tutor.favorite.destroy', $tutorProfile->tutor_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-fav" style="color: #e11d48;" title="เอาออกจากรายการโปรด">♥</button>
                    </form>
                @else
                    <form action="{{ route('tutor.favorite.store', $tutorProfile->tutor_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-fav" title="บันทึกเป็นรายการโปรด">♡</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="tutor-grid-layout">

            <!-- Detail Content Left Side -->
            <div class="tutor-main-detail">

                <!-- รู้จักติวเตอร์ -->
                <div class="detail-card">
                    <h2 class="card-title">🙋‍♂️ รู้จักติวเตอร์</h2>
                    <p style="font-size: 14px; color: #4b5563; line-height: 1.7; margin: 0;">
                        {{ $tutorProfile->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว' }}
                    </p>
                </div>

                <!-- วิชาที่เปิดสอน (ไม่มีราคา) -->
                <div class="detail-card">
                    <h2 class="card-title">📚 วิชาและระดับที่สอน</h2>
                    <div class="subjects-grid">
                        @forelse($tutorProfile->subjects as $subj)
                            <div class="subject-box">
                                <h3 class="subject-name">{{ $subj->subject_name }}</h3>
                                <p class="subject-grade">{{ $subj->description ?? 'เนื้อหาปูพื้นฐาน และตะลุยโจทย์' }}</p>
                            </div>
                        @empty
                            <p class="empty-text">ยังไม่มีรายวิชาที่เปิดสอน</p>
                        @endforelse
                    </div>
                </div>

                <!-- วันและเวลาที่เปิดสอน -->
                <div class="detail-card">
                    <h2 class="card-title">📅 วันและเวลาที่เปิดสอน</h2>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <span><strong>วันจันทร์ - ศุกร์</strong></span>
                            <span>17:00 - 20:00 น.</span>
                        </div>
                        <span class="status-badge status-confirmed">พร้อมสอน</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <span><strong>วันเสาร์ - อาทิตย์</strong></span>
                            <span>09:00 - 18:00 น.</span>
                        </div>
                        <span class="status-badge status-confirmed">พร้อมสอน</span>
                    </div>
                </div>

                <!-- เสียงจากนักเรียน -->
                <div class="detail-card">
                    <h2 class="card-title">💬 เสียงจากนักเรียน</h2>
                    <div class="review-score-summary">
                        <div class="score-num">{{ number_format($tutorProfile->average_rating ?? 5.0, 1) }}</div>
                        <div>
                            <div style="color: #f59e0b; font-size: 16px;">⭐⭐⭐⭐⭐</div>
                            <div style="font-size: 13px; color: #6b7280;">คะแนนเฉลี่ยจากผู้เรียนทั้งหมด</div>
                        </div>
                    </div>
                    <p class="empty-text" style="text-align: center;">ยังไม่มีข้อความรีวิวเพิ่มเติม</p>
                </div>

            </div>

            <!-- Booking Sidebar Right Side -->
            <div class="booking-sidebar">
                <div class="booking-card">
                    <h2 class="booking-title">เริ่มเรียนกับพี่{{ $tutorProfile->user->first_name ?? '' }}</h2>

                    <form action="{{ route('appointments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tutor_id" value="{{ $tutorProfile->tutor_id ?? '' }}">

                        <!-- วิชาที่เลือก -->
                        <div class="form-group">
                            <label>วิชาที่ต้องการเรียน</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">-- เลือกวิชา --</option>
                                @foreach($tutorProfile->subjects as $subj)
                                    <option value="{{ $subj->subject_id ?? $subj->id }}">{{ $subj->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- รูปแบบการเรียน -->
                        <div class="form-group">
                            <label>รูปแบบการเรียน</label>
                            <select name="teaching_mode" class="form-control" required>
                                <option value="online">💻 ออนไลน์ (Online)</option>
                                <option value="onsite">📍 ตัวต่อตัว (Onsite)</option>
                            </select>
                        </div>

                        <!-- วันที่ -->
                        <div class="form-group">
                            <label>วันที่ต้องการเรียน</label>
                            <input type="date" name="appointment_date" class="form-control" required min="{{ date('Y-m-d') }}">
                        </div>

                        <!-- เวลา -->
                        <div class="form-group">
                            <label>เวลาที่ต้องการเรียน</label>
                            <select name="appointment_time" class="form-control" required>
                                <option value="">-- เลือกเวลา --</option>
                                <option value="09:00-11:00">09:00 - 11:00 น.</option>
                                <option value="11:00-13:00">11:00 - 13:00 น.</option>
                                <option value="13:00-15:00">13:00 - 15:00 น.</option>
                                <option value="15:00-17:00">15:00 - 17:00 น.</option>
                                <option value="17:00-19:00">17:00 - 19:00 น.</option>
                            </select>
                        </div>

                        <!-- หมายเหตุ -->
                        <div class="form-group">
                            <label>เรื่องที่ต้องการเน้น (ถ้ามี)</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="ระบุเรื่องที่ต้องการเน้นเพิ่มเติม..."></textarea>
                        </div>

                        <button type="submit" class="btn-booking-submit">
                            ส่งคำขอจองเรียน →
                        </button>
                    </form>

                    <p class="booking-note">ส่งคำขอเรียนโดยตรงเพื่อยืนยันเวลากับติวเตอร์</p>
                </div>
            </div>

        </div>

        <div class="back-link-box">
            <a href="javascript:history.back()" class="btn-back">← ย้อนกลับไปหน้าก่อนหน้า</a>
        </div>

    </div>

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