@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'จัดอันดับติวเตอร์ยอดนิยม - PeerTutor')

<link rel="stylesheet" href="{{ asset('css/tutor-ranking.css') }}?v={{ time() }}">

@section('content')
<div class="ranking-container">
    <!-- Header ส่วนหัวหน้าจัดอันดับ -->
    <div class="ranking-header">
        <div class="header-badge">🏆 TOP TUTORS</div>
        <h1 class="ranking-title">จัดอันดับติวเตอร์ยอดนิยม</h1>
        <p class="ranking-subtitle">รวบรวมติวเตอร์คุณภาพที่มีคะแนนรีวิวสูงสุดและประสบการณ์การสอนการันตี</p>
    </div>

    @if (session('success'))
        <div class="alert-success">
            ✨ {{ session('success') }}
        </div>
    @endif

    @if ($tutors->count() > 0)
        <div class="ranking-list">
            @foreach ($tutors as $index => $tutor)
                @php
                    $rank = $index + 1;
                    $isFavorite = false;
                    if (auth()->check()) {
                        $isFavorite = \App\Domains\TutorProfile\Models\Favorite::where('Users_user_id', auth()->id())
                            ->where('Tutor_profiles_tutor_id', $tutor->tutor_id)
                            ->exists();
                    }
                @endphp

                <div class="tutor-rank-card {{ $rank <= 3 ? 'top-rank rank-' . $rank : '' }}">
                    <!-- Badge แสดงอันดับ -->
                    <div class="rank-badge">
                        @if ($rank === 1)
                            🥇 <span>อันดับ 1</span>
                        @elseif ($rank === 2)
                            🥈 <span>อันดับ 2</span>
                        @elseif ($rank === 3)
                            🥉 <span>อันดับ 3</span>
                        @else
                            #{{ $rank }}
                        @endif
                    </div>

                    <!-- ข้อมูลโปรไฟล์ติวเตอร์ -->
                    <div class="tutor-info-group">
                        <div class="avatar-wrapper">
                            @if ($tutor->user?->profile_picture)
                                <img src="{{ asset('storage/' . $tutor->user->profile_picture) }}" alt="Tutor Avatar" class="tutor-avatar">
                            @else
                                <div class="avatar-placeholder">
                                    {{ mb_substr($tutor->user?->first_name ?? 'T', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="tutor-details">
                            <h2 class="tutor-name">
                                คุณ{{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }}
                            </h2>
                            
                            <div class="meta-row">
                                <span class="rating-badge">
                                    ⭐ {{ number_format($tutor->average_rating ?? 5.0, 2) }} / 5.0
                                </span>
                                <span class="exp-badge">
                                    💼 ประสบการณ์ {{ $tutor->experience_years ?? 0 }} ปี
                                </span>
                                <span class="mode-badge">
                                    @if ($tutor->teaching_mode === 'online')
                                        💻 ออนไลน์
                                    @elseif ($tutor->teaching_mode === 'onsite')
                                        🏫 นัดเจอ (Onsite)
                                    @else
                                        🔄 ออนไลน์ & Onsite
                                    @endif
                                </span>
                            </div>

                            @if(!empty($tutor->bio))
                                <p class="tutor-bio">{{ Str::limit($tutor->bio, 120) }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- ปุ่มการทำงาน -->
                    <div class="action-group">
                       <a href="{{ route('tutor.show', $tutor->tutor_id) }}" class="btn-view-profile">
                            ดูโปรไฟล์
                        </a>

                        @if ($isFavorite)
                            <form action="{{ route('tutor.favorite.destroy', $tutor->tutor_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-fav active" title="ยกเลิกรายการโปรด">
                                    ❤️ เลิกถูกใจ
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tutor.favorite.store', $tutor->tutor_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-fav" title="เพิ่มในรายการโปรด">
                                    🤍 ถูกใจ
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h2>ยังไม่มีข้อมูลติวเตอร์ในระบบ</h2>
            <p>ขณะนี้ยังไม่มีติวเตอร์ที่ลงทะเบียนจัดอันดับ ลองค้นหาติวเตอร์จากวิชาเรียนได้ที่นี่</p>
            <a href="{{ route('tutor.search') }}" class="btn-search-tutor">ค้นหาติวเตอร์เพิ่มเติม</a>
        </div>
    @endif
</div>
@endsection