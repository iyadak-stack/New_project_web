<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerTutor - หน้านักเรียน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}?v={{ time() }}">
    {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}?v={{ time() }}">
    {{-- ปกป้องแก้ให้: path เดิม css/tutor-navbar.css ต้องเป็น CSS/tutor-navbar.css ให้ตรงกับโฟลเดอร์บน Linux --}}
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Navbar -->
    <x-home-navbar />

    <main class="page">
        <!-- Welcome Section -->
        <section class="welcome-box">
            <h1>ยินดีต้อนรับคุณ <b style="color:#e07a5f;">{{ Auth::user()->first_name }}</b> สู่ <b>PeerTutor</b></h1>
            <p>ค้นหาติวเตอร์ที่เข้าใจเนื้อหาเดียวกัน แล้วเริ่มเรียนได้ทันทีด้วยประสบการณ์ที่ใกล้ตัวมากขึ้น</p>
        </section>

        <!-- Top Tutors Section -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 32px; margin-bottom: 12px;">
            <div>
                <h2 class="section-title" style="margin: 0;">ติวเตอร์ยอดนิยม</h2>
                <p class="section-sub" style="margin: 4px 0 0 0;">สำรวจติวเตอร์ที่มีคะแนนสูงสุด</p>
            </div>
            <!-- ปุ่มดูติวเตอร์ทั้งหมด -->
            <a href="{{ route('student.tutors.index') }}" style="color: #e07a5f; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                ดูติวเตอร์ทั้งหมด ➔
            </a>
        </div>

        <div class="grid">
            @if(isset($tutors) && count($tutors) > 0)
                @foreach($tutors as $tutor)
                    <x-tutor-card :tutor="$tutor" />
                @endforeach
            @else
                <p style="color: #94a3b8; font-size: 14px; grid-column: 1 / -1;">ยังไม่มีข้อมูลติวเตอร์ในระบบ</p>
            @endif
        </div>

        <!-- Top Subjects Section -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; margin-bottom: 12px;">
            <div>
                <h2 class="section-title" style="margin: 0;">รายวิชายอดนิยม</h2>
                <p class="section-sub" style="margin: 4px 0 0 0;">รายวิชาที่นักเรียนค้นหามากที่สุดใน <b>PeerTutor</b></p>
            </div>
            <!-- ปุ่มดูรายวิชาทั้งหมด -->
            <a href="{{ route('subjects.index') }}" style="color: #e07a5f; font-weight: 600; font-size: 14px; text-decoration: none;">
                ดูรายวิชาทั้งหมด ➔
            </a>
        </div>

        <div class="grid">
            @if(isset($subjects) && count($subjects) > 0)
                @foreach($subjects as $subject)
                    @php
                        $title = is_array($subject) ? ($subject['title'] ?? '') : ($subject->subject_name ?? $subject->title ?? '');
                        $count = is_array($subject) ? ($subject['count'] ?? 0) : ($subject->tutors_count ?? 0);
                        $description = is_array($subject) ? ($subject['description'] ?? '') : ($subject->description ?? 'รายวิชาคุณภาพเยี่ยม');
                        $tags = is_array($subject) ? ($subject['tags'] ?? []) : ($subject->tags ?? []);
                        $subjectId = is_array($subject) ? ($subject['id'] ?? $subject['subject_id'] ?? null) : ($subject->subject_id ?? $subject->id ?? null);
                    @endphp
                    <div class="card card-subject">
                        <div class="subject-top">
                            <h3>{{ $title }}</h3>
                            <span class="count">{{ $count }}+</span>
                        </div>

                        <p class="desc">{{ $description }}</p>

                        <div class="tags">
                            @if(is_iterable($tags))
                                @foreach($tags as $tag)
                                    <span class="tag">{{ is_array($tag) ? ($tag['name'] ?? $tag) : $tag }}</span>
                                @endforeach
                            @endif
                        </div>

                        <a href="{{ route('subjects.show', $subject->subject_id) }}" class="btn">
                            ดูติวเตอร์วิชานี้
                        </a>
                    </div>
                @endforeach
            @else
                <p style="color: #94a3b8; font-size: 14px; grid-column: 1 / -1;">ยังไม่มีข้อมูลรายวิชาในระบบ</p>
            @endif
        </div>
    </main>


</body>
</html>
