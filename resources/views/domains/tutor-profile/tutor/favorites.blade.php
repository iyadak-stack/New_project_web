@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'รายการโปรด')

@section('content')

<h1>รายการโปรด</h1>
<p>รายการติวเตอร์ที่คุณกดบันทึกไว้</p>

@if ($favorites->count() > 0)
    @foreach ($favorites as $favorite)
        @php
            $tutor = $favorite->tutorProfile;
        @endphp

        @if ($tutor)

            <div>
                <h2>
                    {{ $tutor->user?->first_name }}
                    {{ $tutor->user?->last_name }}
                </h2>

                <p>คะแนนรีวิว:{{ number_format($tutor->average_rating, 2) }} / 5.00</p>

                <p>ประสบการณ์:{{ $tutor->experience_years }} ปี</p>

                <p>
                    รูปแบบการสอน:
                    @if ($tutor->teaching_mode === 'online')
                        ออนไลน์
                    @elseif ($tutor->teaching_mode === 'onsite')
                        นัดเจอ (Onsite)
                    @else
                        ทั้งสองแบบ
                    @endif
                </p>

                <p>แนะนำตัว:{{ $tutor->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว' }}</p>

                <a href="{{ route('tutor.show', $tutor) }}">ดูโปรไฟล์</a>

                <form action="{{ route('tutor.favorite.destroy', $tutor) }}" method="POST">
                    @csrf
                    <button type="submit">ลบออกจากรายการโปรด</button>
                </form>
            </div>

            <hr>
        @endif
    @endforeach
@else

    <h2>ยังไม่มีติวเตอร์ในรายการโปรด</h2>
    <p>ลองค้นหาและเพิ่มติวเตอร์ที่สนใจในรายการโปรด</p>

    <a href="{{ route('tutor.search') }}">ค้นหาติวเตอร์</a>

@endif

@endsection