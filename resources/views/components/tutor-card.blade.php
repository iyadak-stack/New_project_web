@props(['tutor'])

@php
    $isArr = is_array($tutor);

    if ($isArr) {
        $name        = $tutor['name'] ?? 'ติวเตอร์';
        $photo       = $tutor['photo'] ?? null;
        $initials    = $tutor['avatar'] ?? mb_strtoupper(mb_substr($name, 0, 2));
        $hours       = $tutor['teaching_hours'] ?? 0;
        $rating      = $tutor['rating'] ?? '0.0';
        $reviews     = $tutor['reviews'] ?? 0;
        $description = $tutor['description'] ?? 'ไม่มีรายละเอียด';
        $tags        = $tutor['tags'] ?? [];
        $url         = '#';
    } else {
        $user        = $tutor->user;
        $name        = $user->first_name ?? 'ติวเตอร์';
        $photo       = $user->profile_picture ?? null;
        $initials    = mb_strtoupper(mb_substr($name, 0, 2));
        $hours       = (int) floor(($tutor->total_teaching_seconds ?? 0) / 3600);
        $rating      = number_format($tutor->average_rating ?? 0, 1);
        $reviews     = $tutor->reviews_count ?? 0;
        $description = $tutor->bio ?: 'ไม่มีรายละเอียด';
        $tags        = $tutor->subjects->pluck('subject_name')->toArray();
        $url         = route('tutor.show', $tutor->tutor_id);
    }

    // เลือกสีอวาตาร์ (0-4) คงที่ตามชื่อ
    $colorIndex = abs(crc32($name)) % 5;
@endphp

<div class="card card-tutor">
    <div class="tutor-top">
        @if($photo)
            <img src="{{ asset('storage/' . $photo) }}" alt="{{ $name }}" class="avatar">
        @else
            <div class="avatar avatar-initial avatar-c{{ $colorIndex }}">{{ $initials }}</div>
        @endif

        <div>
            <h3>{{ $name }}</h3>
            <p class="small">สอนพิเศษ <b>{{ $hours }}</b> ชั่วโมง</p>
            <p class="rating"><b>{{ $rating }}</b> จาก <b>{{ $reviews }}</b> รีวิว</p>
        </div>
    </div>

    <p class="desc">{{ $description }}</p>

    @if(count($tags) > 0)
        <div class="tags">
            @foreach($tags as $tag)
                <span class="tag">{{ is_array($tag) ? ($tag['name'] ?? '') : $tag }}</span>
            @endforeach
        </div>
    @endif

    <div class="card-actions">
        @if ($isArr || ($tutor->approval_status === 'approved' && $tutor->user->is_active))
        <a href="{{ $url }}" class="btn">ดูโปรไฟล์</a>
        @else
            {{-- รายการรวมแสดงทุกคน แต่ผู้ที่ยังไม่อนุมัติยังจองเรียนไม่ได้ --}}
            <span class="small">
                @if (!$tutor->user->is_active)
                    บัญชีถูกระงับ
                @elseif ($tutor->approval_status === 'rejected')
                    ยังไม่ผ่านอนุมัติ
                @else
                    รอแอดมินอนุมัติ
                @endif
            </span>
        @endif
    </div>
</div>
