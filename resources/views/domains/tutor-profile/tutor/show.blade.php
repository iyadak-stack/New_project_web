<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>รายละเอียดติวเตอร์ - PeerTutor</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/tutor-detail.css') }}?v={{ time() }}">
    </head>
    <body>
        <!-- Header / Navbar ด้านบน --><x-home-navbar />
        <main class="tutor-detail-container">
            @if(session('report_success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('report_success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
                </div>
            @endif
            <!-- Breadcrumb & ปุ่มย้อนกลับ --><div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <a href="{{ route('home') }}" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">← กลับหน้าหลัก</a>
            </div>
            @php
            $tutorUser = $tutorProfile->user;
        @endphp
            <!-- Header Badge Profile Card ของติวเตอร์คนนี้ --><div class="tutor-header-card">
                <div class="tutor-header-flex">
                    <div class="tutor-avatar">
                        <!-- รูปโปรไฟล์ของติวเตอร์ -->@if ($tutorUser?->profile_picture)
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
                        <h1 class="tutor-name">พี่{{ $tutorProfile->user?->first_name }} {{ $tutorProfile->user?->last_name }}</h1>
                        <p class="tutor-headline">ประสบการณ์สอน {{ $tutorProfile->experience_years }} ปี</p>
                        <div class="tutor-stats">
                            <span class="rating">⭐ {{ number_format($tutorProfile->average_rating, 1) }} ({{ $tutorProfile->reviews->count() }} รีวิว)</span>
                        </div>
                    </div>
                    <div class="tutor-fav-action">
                        @auth
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
                        @if((string) Auth::id() !== (string) $tutorProfile->Users_user_id)
                            <a href="{{ route('reports.create', ['tutor_id' => $tutorProfile->tutor_id]) }}" class="btn btn-outline-danger btn-sm mt-2">
                                รายงานติวเตอร์
                            </a>
                        @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">เข้าสู่ระบบเพื่อบันทึกรายการโปรด</a>
                        @endauth
                    </div>
                </div>
            </div>
            <!-- Main Grid Layout (2 Columns) --><div class="tutor-grid-layout">
                <!-- Left Column: Details --><div class="tutor-main-content">
                    <!-- วิชาและระดับชั้นที่สอน --><div class="detail-card">
                        <h2 class="card-title">วิชาและระดับชั้นที่สอน</h2>
                        <div class="subjects-grid">
                            @forelse ($tutorProfile->subjects as $subject)
                                <div class="subject-box">
                                    <h3 class="subject-name">{{ $subject->subject_name }}</h3>
                                    <p class="subject-grade">{{ $subject->pivot->grade_level ?? 'ระดับ ม.ต้น / ม.ปลาย / มหาวิทยาลัย' }}</p>
                                </div>
                            @empty
                                <p class="empty-text">ยังไม่ได้ระบุวิชาที่สอน</p>
                            @endforelse
                        </div>
                    </div>
                    <!-- วันและเวลาที่เปิดสอน --><div class="detail-card">
                        <h2 class="card-title">วันและเวลาที่เปิดสอน</h2>
                        @forelse($availabilities as $availability)
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    {{ $availability->start_datetime->locale('th')->translatedFormat('lที่ j F Y เวลา H:i') }} – {{ $availability->end_datetime->format('H:i') }} น.
                                </div>
                                <span class="status-badge">พร้อมรับจอง</span>
                            </div>
                        @empty
                            <p class="empty-text">ตอนนี้ยังไม่มีช่วงเวลาว่างให้เลือก</p>
                        @endforelse
                    </div>
                    <!-- รีวิวจากผู้เรียน --><div class="detail-card">
                        <h2 class="card-title">เสียงจากนักเรียน</h2>
                        <div class="review-score-summary">
                            <div class="score-num">
                                {{ number_format($tutorProfile->average_rating, 1) }}
                            </div>
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
                <!-- การ์ดเลือกเวลาเรียน --><div class="booking-section-card" style="background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #f3f4f6; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-top: 24px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">เลือกเวลาเรียน</h3>
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
                            <strong>สำเร็จ!</strong>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('appointments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tutor_id" value="{{ $tutorProfile->tutor_id }}">
                        <!-- 1. วิชาที่ต้องการเรียน (ดึงจาก Seeder / DB) --><div class="mb-3">
                            <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">
                                วิชาที่ต้องการเรียน
                            </label>
                            <select name="subject_id" class="form-select" style="width: 100%; border-radius: 12px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background-color: #fff;" required>
                                <option value="" disabled selected>-- เลือกวิชาที่ต้องการเรียน --</option>
                                <!-- ดึงเฉพาะวิชาที่ติวเตอร์คนนี้เปิดสอน (ถ้ามี) หรือดึงวิชาทั้งหมด -->@if(isset($tutorProfile->subjects) && $tutorProfile->subjects->count() > 0)
                                    @foreach($tutorProfile->subjects as $sub)
                                        <option value="{{ $sub->subject_id }}">
                                            {{ $sub->subject_name }}
                                        </option>
                                    @endforeach
                                @elseif(isset($allSubjects))
                                    @foreach($allSubjects as $sub)
                                        <option value="{{ $sub->subject_id }}">
                                            {{ $sub->subject_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="availability_id" class="form-label">
                                วันและเวลาที่ติวเตอร์สะดวก
                            </label>
                            <select id="availability_id" name="availability_id" class="form-select" required>
                                <option value="" disabled {{ old('availability_id') ? '' : 'selected' }}>-- เลือกวันและเวลา --</option>
                                @foreach($availabilities as $availability)
                                    <option value="{{ $availability->availability_id }}" @selected(old('availability_id') === $availability->availability_id)>
                                    {{ $availability->start_datetime->locale('th')->translatedFormat('lที่ j F Y เวลา H:i') }} – {{ $availability->end_datetime->format('H:i') }} น.
                                    </option>
                                @endforeach
                            </select>
                            @if($availabilities->isEmpty())
                                <div class="form-text">
                                    ตอนนี้ยังไม่มีช่วงเวลาว่างให้เลือก
                                </div>
                            @endif
                            @error('availability_id')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <!-- 2. ระดับชั้น --><div class="mb-3">
                            <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">
                                ระดับชั้น
                            </label>
                            <select name="grade_level" class="form-select" style="width: 100%; border-radius: 12px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background-color: #fff;" required>
                                <option value="ประถมศึกษา">ประถมศึกษา</option>
                                <option value="ม.ต้น (ม.1 - ม.3)" selected>ม.ต้น (ม.1 - ม.3)</option>
                                <option value="ม.ปลาย (ม.4 - ม.6)">ม.ปลาย (ม.4 - ม.6)</option>
                                <option value="มหาวิทยาลัย">มหาวิทยาลัย</option>
                                <option value="บุคคลทั่วไป">บุคคลทั่วไป</option>
                            </select>
                        </div>
                        <!-- 3. รูปแบบการเรียน --><div class="mb-4">
                            <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">
                                รูปแบบการเรียน
                            </label>
                            <div style="width: 100%; padding: 10px; border: 1px solid #e07a5f; border-radius: 12px; text-align: center; color: #e07a5f; font-weight: 600; font-size: 14px; background: #fffaf8;">
                                {{ ucfirst($tutorProfile->teaching_mode ?? 'ออนไลน์') }}
                            </div>
                            <input type="hidden" name="teaching_mode" value="{{ $tutorProfile->teaching_mode ?? 'online' }}">
                        </div>
                        @auth
                        <!-- ปุ่มส่งคำขอ --><button type="submit" class="btn-submit-request" @disabled($availabilities->isEmpty()) style="width: 100%; background: #e07a5f; color: #ffffff; border: none; padding: 12px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    ส่งคำขอจองเรียน
                        </button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">เข้าสู่ระบบหรือสมัครสมาชิกเพื่อจองเรียน</a>
                        @endauth
                    </form>
                </div>
            </main>
            <x-login-modal />
            <x-register-modal />
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <!-- เปิด/ปิดแผงโปรไฟล์ --><script>
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
