@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'หน้าแรก')

@section('content')

    <div class="home-hero">
        <div>
            <h1>ยินดีต้อนรับสู่ PeerTutor</h1>
            <p>
                ค้นหาติวเตอร์ สำรวจรายวิชา และจัดการคลาสเรียนของคุณ
            </p>

            <a href="{{ route('tutor.search') }}" class="btn btn-primary">
                ค้นหาติวเตอร์ / รายวิชา
            </a>
        </div>
    </div>

    <section class="home-section">
        <div class="section-header">
            <div>
                <h2>ติวเตอร์ยอดนิยม</h2>
                <p>
                    สำรวจติวเตอร์ที่มีคะแนนรีวิวสูงสุดของเรา
                </p>
            </div>

            <a href="{{ route('tutor.ranking') }}" class="btn btn-outline-primary">
                ดูการจัดอันดับ
            </a>
        </div>

        @if ($topTutors->count() > 0)
            <div class="row g-4">
                @foreach ($topTutors as $tutor)
                    <div class="col-md-6 col-lg-4">

                        <div class="card tutor-card h-100">
                            <div class="card-body">
                                <div class="tutor-rank">
                                    #{{ $loop->iteration }}
                                </div>

                                <h3 class="tutor-name">
                                    {{ $tutor->user->name ?? 'ไม่ระบุชื่อติวเตอร์' }}
                                </h3>

                                <div class="tutor-info">
                                    <strong>คะแนนรีวิว</strong>
                                    <span>
                                        {{ number_format($tutor->average_rating, 2) }} / 5.00
                                    </span>
                                </div>

                                <div class="tutor-info">
                                    <strong>ประสบการณ์</strong>
                                    <span>
                                        {{ $tutor->experience_years }} ปี
                                    </span>
                                </div>

                                <div class="tutor-info">
                                    <strong>รูปแบบการสอน</strong>
                                    <span>
                                        {{ ucfirst($tutor->teaching_mode) }}
                                    </span>
                                </div>

                                <div class="tutor-subjects">
                                    <strong>รายวิชาที่สอน</strong>

                                    <div class="mt-2">
                                        @if ($tutor->subjects->count() > 0)
                                            @foreach ($tutor->subjects as $subject)
                                                <span class="badge bg-light text-dark border me-1 mb-1">
                                                    {{ $subject->subject_name }}
                                                </span>
                                            @endforeach

                                        @else
                                            <span class="text-muted">
                                                ยังไม่ได้ระบุวิชาที่สอน
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('tutor.show', $tutor) }}" class="btn btn-primary w-100">
                                        ดูรายละเอียดติวเตอร์
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-secondary">
                ยังไม่มีข้อมูลติวเตอร์ในขณะนี้
            </div>
        @endif
    </section>

    <section class="home-section">
        <div class="section-header">
            <div>
                <h2>รายวิชายอดนิยม</h2>
                <p>
                    สำรวจรายวิชาที่กำลังเปิดสอนโดยติวเตอร์ของเรา
                </p>
            </div>

            <a href="{{ route('tutor.search') }}" class="btn btn-outline-primary">
                ค้นหารายวิชา
            </a>
        </div>

        @if ($topSubjects->count() > 0)
            <div class="row g-4">
                @foreach ($topSubjects as $subject)
                    <div class="col-md-6 col-lg-4">
                        <div class="card subject-card h-100">
                            <div class="card-body">
                                <div class="subject-rank">
                                    #{{ $loop->iteration }}
                                </div>

                                <h3 class="subject-name">{{ $subject->subject_name }}</h3>

                                <p class="text-muted mb-0">
                                    ติวเตอร์ที่สอนวิชานี้:

                                    <strong>
                                        {{ $subject->tutors->count() }} ท่าน
                                    </strong>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-secondary">
                ยังไม่มีข้อมูลรายวิชาในขณะนี้
            </div>
        @endif
    </section>

    <section class="home-section">
        <div class="section-header">
            <div>
                <h2>ตารางเรียนที่กำลังจะมาถึง</h2>
                <p>
                    รายการคลาสเรียนของคุณจะแสดงที่นี่
                </p>
            </div>
        </div>

        <div class="card upcoming-card">
            <div class="card-body">
                <p class="mb-0 text-muted">
                    ส่วนนี้จะเชื่อมต่อกับระบบนัดหมายและตารางเรียน (Appointment & Schedule System) ของทีมต่อไป
                </p>
            </div>
        </div>
    </section>

@endsection