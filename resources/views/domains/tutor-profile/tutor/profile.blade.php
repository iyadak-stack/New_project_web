@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'โปรไฟล์ติวเตอร์')

@section('content')

<h1>โปรไฟล์ติวเตอร์</h1>
<p>จัดการและดูข้อมูลโปรไฟล์ติวเตอร์ของคุณ</p>

@if ($tutorProfile)

    <h2>ข้อมูลโปรไฟล์</h2>
    <p>แนะนำตัว (Bio): <strong>{{ $tutorProfile->bio ?: 'ยังไม่ได้เพิ่มข้อมูลแนะนำตัว' }}</strong></p>
    <p>ประสบการณ์: <strong>{{ $tutorProfile->experience_years }} ปี</strong></p>
    <p>คะแนนรีวิว: <strong>{{ number_format($tutorProfile->average_rating, 2) }} / 5.00</strong></p>
    <p>
        รูปแบบการสอน:
        <strong>
            @if ($tutorProfile->teaching_mode === 'online') ออนไลน์
            @elseif ($tutorProfile->teaching_mode === 'onsite') นัดเจอ (Onsite)
            @else ทั้งสองแบบ
            @endif
        </strong>
    </p>

    <p><a href="{{ route('tutor.profile.edit') }}">แก้ไขโปรไฟล์</a></p>

@else
    <h2>ไม่พบข้อมูลโปรไฟล์ติวเตอร์</h2>
    <p>คุณยังไม่มีข้อมูลโปรไฟล์สำหรับติวเตอร์</p>

@endif

@endsection