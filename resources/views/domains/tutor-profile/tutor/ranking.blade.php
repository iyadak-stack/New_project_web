@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'จัดอันดับติวเตอร์')

@section('content')

<h1>จัดอันดับติวเตอร์</h1>
<p>จัดอันดับติวเตอร์ตามคะแนนรีวิวและประสบการณ์</p>

@if (session('success'))
    <div>
        {{ session('success') }}
    </div>
@endif

@if ($tutors->count() > 0)
    @foreach ($tutors as $index => $tutor)
        <div>
            <p>#{{ $index + 1 }}</p>
            <h2>{{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }}</h2>
            <p>คะแนนรีวิว:<strong>{{ number_format($tutor->average_rating, 2) }} / 5.00</strong></p>
            <p>ประสบการณ์:<strong>{{ $tutor->experience_years }} ปี</strong></p>
            <p>
                รูปแบบการสอน:
                <strong>
                    @if ($tutor->teaching_mode === 'online') ออนไลน์
                    @elseif ($tutor->teaching_mode === 'onsite') นัดเจอ (Onsite)
                    @else ทั้งสองแบบ
                    @endif
                </strong>
            </p>

            @php
                $isFavorite = auth()->user()
                    ->favorites()
                    ->where('favoritable_type', \App\Models\TutorProfile::class)
                    ->where('favoritable_id', $tutor->id)
                    ->exists();
            @endphp
            <div>
                <a href="{{ route('tutor.show', $tutor) }}">ดูโปรไฟล์ติวเตอร์</a>
                @if ($isFavorite)
                    <form action="{{ route('tutor.favorite.destroy', $tutor) }}" method="POST">
                        @csrf
                        <button type="submit">ยกเลิกรายการโปรด</button>
                    </form>
                @else
                    <form action="{{ route('tutor.favorite.store', $tutor) }}" method="POST">
                        @csrf
                        <button type="submit">เพิ่มในรายการโปรด</button>
                    </form>
                @endif
            </div>
        </div>
        <hr>
    @endforeach
@else

    <h2>ยังไม่มีข้อมูลติวเตอร์</h2>
    <p>ยังไม่มีข้อมูลสำหรับจัดอันดับติวเตอร์ในขณะนี้</p>

    <a href="{{ route('tutor.search') }}">ค้นหาติวเตอร์</a>

@endif

@endsection