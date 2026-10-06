@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'จัดอันดับติวเตอร์')

@section('content')

    <div class="ranking-header mb-4">
        <h1>จัดอันดับติวเตอร์</h1>
        <p>
            จัดอันดับติวเตอร์ตามคะแนนรีวิวและประสบการณ์
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($tutors->count() > 0)
        <div class="ranking-list">
            @foreach ($tutors as $index => $tutor)
                <div class="card ranking-card mb-3">
                    <div class="card-body">
                        <div class="ranking-number">
                            #{{ $index + 1 }}
                        </div>
                        <h2 class="ranking-name h4">{{ $tutor->user->name ?? 'ไม่ทราบชื่อติวเตอร์' }}</h2>

                        <div class="ranking-info">
                            <div>
                                <span>คะแนนรีวิว</span>

                                <strong>
                                    {{ number_format($tutor->average_rating, 2) }} / 5.00
                                </strong>
                            </div>

                            <div>
                                <span>ประสบการณ์</span>

                                <strong>
                                    {{ $tutor->experience_years }} ปี
                                </strong>
                            </div>

                            <div>
                                <span>รูปแบบการสอน</span>

                                <strong>
                                    @if($tutor->teaching_mode === 'online') ออนไลน์
                                    @elseif($tutor->teaching_mode === 'onsite') นัดเจอ (Onsite)
                                    @else ทั้งสองแบบ
                                    @endif
                                </strong>
                            </div>
                        </div>

                        @php
                            $isFavorite = auth()->user()
                                ->favorites()
                                ->where('favoritable_type', \App\Models\TutorProfile::class)
                                ->where('favoritable_id', $tutor->id)
                                ->exists();
                        @endphp
                        <div class="ranking-actions mt-3">

                            <a href="{{ route('tutor.show', $tutor) }}" class="btn btn-primary">ดูโปรไฟล์ติวเตอร์</a>
                            @if ($isFavorite)
                                <form action="{{ route('tutor.favorite.destroy', $tutor) }}" method="POST" class="d-inline">
                                    @csrf

                                    <button type="submit" class="btn btn-outline-danger">ยกเลิกรายการโปรด</button>
                                </form>

                            @else
                                <form action="{{ route('tutor.favorite.store', $tutor) }}" method="POST" class="d-inline">
                                    @csrf

                                    <button type="submit" class="btn btn-outline-primary">เพิ่มในรายการโปรด</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <div class="empty-ranking text-center py-5">
            <h2>ยังไม่มีข้อมูลติวเตอร์</h2>
            <p>
                ยังไม่มีข้อมูลสำหรับจัดอันดับติวเตอร์ในขณะนี้
            </p>

            <a href="{{ route('tutor.search') }}" class="btn btn-primary">ค้นหาติวเตอร์</a>
        </div>
    @endif

@endsection