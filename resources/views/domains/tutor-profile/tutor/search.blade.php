@extends('layouts.tutor')

@section('title', 'ค้นหาติวเตอร์ หรือ รายวิชา')

@section('content')

<h1>ค้นหาติวเตอร์ หรือ รายวิชา</h1>

<form action="{{ route('tutor.search') }}" method="GET">
    <div>
        <label for="search">ค้นหา</label>
        <input
            type="text"
            id="search"
            name="search"
            value="{{ $search }}"
            placeholder="พิมพ์ชื่อติวเตอร์ หรือ รายวิชา..."
        >

        <button type="submit">ค้นหา</button>
    </div>
</form>

@if ($search !== '')

    <hr>

    <h2>ผลการค้นหา</h2>
    <p>ผลการค้นหาสำหรับ:<strong>"{{ $search }}"</strong></p>

    @if ($tutors->count() > 0)
        <h3>ติวเตอร์</h3>

        <p>พบ {{ $tutors->count() }} คน</p>

        @foreach ($tutors as $tutor)
            <div>
                <h4>{{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }}</h4>

                <p><strong>คะแนนรีวิว:</strong>{{ number_format($tutor->average_rating, 2) }} / 5.00</p>
                <p><strong>ประสบการณ์:</strong>{{ $tutor->experience_years }} ปี</p>
                <p><strong>รูปแบบการสอน:</strong>{{ ucfirst($tutor->teaching_mode) }}</p>
                <p><strong>ประวัติโดยย่อ:</strong>{{ $tutor->bio ?? 'ไม่มีข้อมูลประวัติ' }}</p>
                <p><strong>รายวิชาที่สอน:</strong></p>

                @if ($tutor->subjects->count() > 0)

                    @foreach ($tutor->subjects as $subject)
                        <span>{{ $subject->subject_name }}</span>
                    @endforeach

                @else
                    <p>ยังไม่ได้ระบุวิชาที่สอน</p>
                @endif

                <p>
                    <a href="{{ route('tutor.show', $tutor) }}"> ดูรายละเอียดติวเตอร์</a>
                </p>
            </div>
            <hr>
        @endforeach
    @endif

    @if ($subjects->count() > 0)
        <h3>รายวิชา</h3>
        <p>พบ {{ $subjects->count() }} วิชา</p>

        @foreach ($subjects as $subject)
            <div>
                <h4>{{ $subject->subject_name }}</h4>
                <p> ติวเตอร์ที่สอนวิชานี้:<strong>{{ $subject->tutors->count() }} คน</strong></p>

                @if ($subject->tutors->count() > 0)
                    <p><strong>รายชื่อติวเตอร์</strong></p>

                    @foreach ($subject->tutors as $tutor)
                        <div>
                            <a href="{{ route('tutor.show', $tutor) }}">
                                {{ $tutor->user?->first_name }}
                                {{ $tutor->user?->last_name }}
                            </a>

                            <span>คะแนน:{{ number_format($tutor->average_rating, 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <p>ยังไม่มีติวเตอร์สำหรับวิชานี้</p>
                @endif
            </div>
            <hr>
        @endforeach
    @endif

    @if ($tutors->count() === 0 && $subjects->count() === 0)
        <p>ไม่พบข้อมูลติวเตอร์หรือรายวิชาที่คุณค้นหา</p>
    @endif

@endif

@endsection
