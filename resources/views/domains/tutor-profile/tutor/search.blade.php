@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'ค้นหาติวเตอร์ หรือ รายวิชา')

@section('content')

    <div class="search-header">
        <h1>ค้นหาติวเตอร์ หรือ รายวิชา</h1>
        <p>
            ค้นหาติวเตอร์หรือรายวิชาที่ต้องการได้ผ่านช่องค้นหาเดียว
        </p>
    </div>

    <div class="search-box">
        <form action="{{ route('tutor.search') }}" method="GET">
            <div class="row g-2">

                <div class="col-md-10">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="พิมพ์ชื่อติวเตอร์ หรือ รายวิชา..."
                    >
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        ค้นหา
                    </button>
                </div>

            </div>
        </form>
    </div>

    @if ($search !== '')
        <div class="results-header">
            <h2>ผลการค้นหา</h2>

            <p>
                ผลการค้นหาสำหรับ:
                <strong>"{{ $search }}"</strong>
            </p>
        </div>

        @if ($tutors->count() > 0)
            <section class="result-section">
                <div class="section-section-title">
                    <h3>ติวเตอร์</h3>

                    <span class="result-count">
                        พบ {{ $tutors->count() }} ท่าน
                    </span>
                </div>

                <div class="row g-4">
                    @foreach ($tutors as $tutor)
                        <div class="col-md-6 col-lg-4">
                            <div class="card tutor-card h-100">
                                <div class="card-body">
                                    <h4 class="tutor-name">
                                        {{ $tutor->user->name ?? 'ไม่ระบุชื่อติวเตอร์' }}
                                    </h4>

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

                                    <div class="tutor-bio">
                                        <strong>ประวัติโดยย่อ</strong>

                                        <p>
                                            {{ $tutor->bio ?? 'ไม่มีข้อมูลประวัติ' }}
                                        </p>
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
                                                <p class="text-muted mb-0">
                                                    ยังไม่ได้ระบุวิชาที่สอน
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- ดูโปรไฟล์ติวเตอร์ --}}
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
            </section>
        @endif

        @if ($subjects->count() > 0)
            <section class="result-section">
                <div class="section-title">
                    <h3>รายวิชา</h3>

                    <span class="result-count">
                        พบ {{ $subjects->count() }} วิชา
                    </span>
                </div>

                <div class="row g-4">
                    @foreach ($subjects as $subject)
                        <div class="col-md-6 col-lg-4">
                            <div class="card subject-card h-100">
                                <div class="card-body">
                                    <h4 class="subject-name">
                                        {{ $subject->subject_name }}
                                    </h4>

                                    <p class="text-muted">
                                        ติวเตอร์ที่สอนวิชานี้:
                                        <strong>
                                            {{ $subject->tutors->count() }} ท่าน
                                        </strong>
                                    </p>

                                    @if ($subject->tutors->count() > 0)
                                        <div class="subject-tutors">
                                            <strong>รายชื่อติวเตอร์</strong>

                                            <div class="mt-2">
                                                @foreach ($subject->tutors as $tutor)
                                                    <div class="tutor-result">
                                                        <a href="{{ route('tutor.show', $tutor) }}" class="tutor-link">
                                                            {{ $tutor->user->name ?? 'ไม่ระบุชื่อติวเตอร์' }}
                                                        </a>

                                                        <span class="text-muted">
                                                            คะแนน:
                                                            {{ number_format($tutor->average_rating, 2) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">
                                            ยังไม่มีติวเตอร์สำหรับวิชานี้
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($tutors->count() === 0 && $subjects->count() === 0)
            <div class="alert alert-secondary no-results">
                ไม่พบข้อมูลติวเตอร์หรือรายวิชาที่คุณค้นหา
            </div>
        @endif
    @endif
@endsection