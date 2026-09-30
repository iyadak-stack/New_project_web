@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'รายละเอียดติวเตอร์')

@section('content')

    <h1>
        {{ $tutorProfile->user?->first_name }}
        {{ $tutorProfile->user?->last_name }}
    </h1>

    <p>ดูข้อมูลประวัติ รายวิชาที่สอน ตารางเวลาที่สะดวก และตัวเลือกการจองเรียน</p>
    <hr>
    <h2>ข้อมูลติวเตอร์</h2>
    <p>คะแนนรีวิว: {{ number_format($tutorProfile->average_rating, 2) }} / 5.00</p>
    <p>ประสบการณ์: {{ $tutorProfile->experience_years }} ปี</p>
    <p>รูปแบบการสอน: {{ ucfirst($tutorProfile->teaching_mode) }}</p>
    <hr>
    <h2>เกี่ยวกับติวเตอร์</h2>
    <p>{{ $tutorProfile->bio ?? 'ไม่มีข้อมูลประวัติ' }}</p>
    <hr>
    <h2>รายวิชาที่เปิดสอน</h2>

    @if ($tutorProfile->subjects->count() > 0)
        @foreach ($tutorProfile->subjects as $subject)
            <p>{{ $subject->subject_name }}</p>
        @endforeach
    @else
        <p>ยังไม่ได้ระบุวิชาที่สอน</p>
    @endif
    <hr>

    <h2>รีวิวจากผู้เรียน</h2>
    @if ($tutorProfile->reviews->count() > 0)
        @foreach ($tutorProfile->reviews as $review)
            <div>
                <p>คะแนน: {{ $review->rating }} / 5</p>
                <p>ความคิดเห็น: {{ $review->Comment }}</p>
            </div>
            <hr>
        @endforeach
    @else
        <p>ยังไม่มีรีวิว</p>
    @endif

    <h2>การนัดหมาย</h2>

    @if ($tutorProfile->appointments->count() > 0)
        @foreach ($tutorProfile->appointments as $appointment)
            <div>
                <p> วันที่: {{ $appointment->start_datetime }}</p>
                <p>ถึง:{{ $appointment->end_datetime }}</p>
                <p>สถานะ:{{ $appointment->status }}</p>
            </div>
            <hr>
        @endforeach
    @else
        <p>ยังไม่มีการนัดหมาย</p>
    @endif
    <hr>

    <h2>ตารางเวลาสอนที่ว่าง</h2>
    <p>ตารางเวลาสอนที่ว่างของติวเตอร์จะแสดงที่นี่</p>

    <p>ส่วนนี้จะเชื่อมต่อกับระบบจัดการเวลาว่าง (Availability System) ของทีม</p><hr>

    <h2>จองเวลาเรียน</h2>
    <p>เลือกรอบเวลาที่ต้องการเพื่อทำการจองเรียนกับติวเตอร์ท่านนี้</p>

    <button type="button" disabled> จองเรียน </button>

    <p>ระบบจองเรียนจะเชื่อมต่อกับระบบนัดหมาย (Appointment System) ของทีม</p><hr>

    <h2>รายการโปรด</h2>
    @if ($isFavorite)
        <p>ติวเตอร์คนนี้อยู่ในรายการโปรดของคุณแล้ว</p>
        <form
            action="{{ route('tutor.favorite.destroy', $tutorProfile) }}"
            method="POST"
        >
            @csrf
            <button type="submit">ลบออกจากรายการโปรด</button>
        </form>
    @else
        <p>บันทึกติวเตอร์คนนี้ไว้ในรายการโปรดของคุณ</p>

        <form
            action="{{ route('tutor.favorite.store', $tutorProfile) }}"
            method="POST"
        >
            @csrf
            <button type="submit">เพิ่มในรายการโปรด</button>
        </form>
    @endif

    <hr>

    <a href="{{ route('tutor.search') }}">กลับไปหน้าค้นหา</a>

@endsection