@extends('layouts.tutor')

@section('title', 'รายละเอียดติวเตอร์')

@section('content')

    {{-- ส่วนหัวของหน้า --}}
    <div class="profile-header">
        <div>
            <h1>{{ $tutorProfile->user->name ?? 'ไม่ระบุชื่อติวเตอร์' }}</h1>

            <p class="profile-description">
                ดูข้อมูลประวัติ รายวิชาที่สอน ตารางเวลาที่สะดวก และตัวเลือกการจองเรียน
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card profile-card mb-4">
                <div class="card-body">
                    <h2 class="section-title">ข้อมูลติวเตอร์</h2>
                    <div class="profile-info-grid">
                        <div class="info-item">
                            <span class="info-label">คะแนนรีวิว</span>

                            <strong>
                                {{ number_format($tutorProfile->average_rating, 2) }} / 5.00
                            </strong>
                        </div>

                        <div class="info-item">
                            <span class="info-label">ประสบการณ์</span>

                            <strong>
                                {{ $tutorProfile->experience_years }} ปี
                            </strong>
                        </div>

                        <div class="info-item">
                            <span class="info-label">รูปแบบการสอน</span>

                            <strong>
                                {{ ucfirst($tutorProfile->teaching_mode) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card profile-card mb-4">
                <div class="card-body">
                    <h2 class="section-title">เกี่ยวกับติวเตอร์</h2>
                    <p class="about-text">
                        {{ $tutorProfile->bio ?? 'ไม่มีข้อมูลประวัติ' }}
                    </p>
                </div>
            </div>

            <div class="card profile-card mb-4">
                <div class="card-body">
                    <h2 class="section-title">รายวิชาที่เปิดสอน</h2>
                    @if ($tutorProfile->subjects->count() > 0)
                        <div>
                            @foreach ($tutorProfile->subjects as $subject)
                                <span class="badge bg-light text-dark border subject-badge">
                                    {{ $subject->subject_name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            ยังไม่ได้ระบุวิชาที่สอน
                        </p>
                    @endif
                </div>
            </div>

            <div class="card profile-card mb-4">
                <div class="card-body">
                    <h2 class="section-title">ตารางเวลาสอนที่ว่าง</h2>
                    <div class="schedule-placeholder">
                        <p>
                            ตารางเวลาสอนที่ว่างของติวเตอร์จะแสดงที่นี่
                        </p>

                        <small>
                            ส่วนนี้จะเชื่อมต่อกับระบบจัดการเวลาว่าง (Availability System) ของทีมต่อไป
                        </small>
                    </div>
                </div>
            </div>

            <div class="card profile-card">
                <div class="card-body">
                    <h2 class="section-title">จองเวลาเรียน</h2>
                    <p class="text-muted">
                        เลือกรอบเวลาที่ต้องการเพื่อทำการจองเรียนกับติวเตอร์ท่านนี้
                    </p>

                    <button type="button" class="btn btn-primary" disabled>
                        จองเรียน
                    </button>

                    <p class="booking-note">
                        ระบบจองเรียนจะเชื่อมต่อกับระบบนัดหมาย (Appointment System) ของทีมต่อไป
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card profile-card favorite-card">
                <div class="card-body">
                    <h2 class="section-title">ติวเตอร์รายการโปรด</h2>
                    @if ($isFavorite)

                        <p class="favorite-status">
                            ติวเตอร์คนนี้อยู่ในรายการโปรดของคุณแล้ว
                        </p>

                        <form action="{{ route('tutor.favorite.destroy', $tutorProfile) }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-outline-danger w-100">
                                ลบออกจากรายการโปรด
                            </button>
                        </form>
                    @else
                        <p class="favorite-status">
                            บันทึกติวเตอร์คนนี้ไว้ในรายการโปรดของคุณ
                        </p>

                        <form action="{{ route('tutor.favorite.store', $tutorProfile) }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-outline-primary w-100">
                                เพิ่มในรายการโปรด
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="back-section">
        <a href="{{ route('tutor.search') }}" class="back-link">
            กลับไปหน้าค้นหา
        </a>
    </div>
@endsection