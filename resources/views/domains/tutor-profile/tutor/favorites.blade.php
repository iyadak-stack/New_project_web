@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'ติวเตอร์รายการโปรด')

@section('content')

    <div class="favorites-header mb-4">
        <h1>ติวเตอร์รายการโปรด</h1>
        <p>
            รายชื่อติวเตอร์ที่คุณบันทึกไว้ในรายการโปรด
        </p>
    </div>

    @if ($favorites->count() > 0)
        <div class="row g-4">
            @foreach ($favorites as $favorite)
                @php
                    $tutor = $favorite->favoritable;
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card favorite-card h-100">
                        <div class="card-body">
                            <h2 class="tutor-name h4">{{ $tutor->user->name ?? 'ไม่ทราบชื่อติวเตอร์' }}</h2>

                            <div class="tutor-info mb-1">
                                <strong>คะแนนรีวิว:</strong>

                                <span>
                                    {{ number_format($tutor->average_rating, 2) }} / 5.00
                                </span>
                            </div>

                            <div class="tutor-info mb-1">
                                <strong>ประสบการณ์:</strong>

                                <span>
                                    {{ $tutor->experience_years }} ปี
                                </span>
                            </div>

                            <div class="tutor-info mb-1">
                                <strong>รูปแบบการสอน:</strong>

                                <span>
                                    @if($tutor->teaching_mode === 'online') ออนไลน์
                                    @elseif($tutor->teaching_mode === 'onsite') นัดเจอ (Onsite)
                                    @else ทั้งสองแบบ
                                    @endif
                                </span>
                            </div>

                            <div class="tutor-bio my-2">
                                <strong>แนะนำตัว:</strong>
                                <p class="mb-0">
                                    {{ $tutor->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว' }}
                                </p>
                            </div>

                            <div class="favorite-actions mt-3 d-flex gap-2">
                                <a href="{{ route('tutor.show', $tutor) }}" class="btn btn-primary btn-sm">ดูโปรไฟล์</a>

                                <form action="{{ route('tutor.favorite.destroy', $tutor) }}" method="POST">
                                    @csrf

                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        ลบจากรายการโปรด
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-favorites text-center py-5">
            <h2>ยังไม่มีติวเตอร์ในรายการโปรด</h2>
            <p>
                คุณยังไม่ได้เพิ่มติวเตอร์คนไหนเข้าในรายการโปรด
            </p>

            <a href="{{ route('tutor.search') }}" class="btn btn-primary">ค้นหาติวเตอร์</a>
        </div>
    @endif

@endsection