<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PeerTutor - ค้นหาติวเตอร์</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/home.css') }}?v={{ time() }}">
        {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}?v={{ time() }}">
    </head>
    <body>
        <!-- Navbar --><header class="navbar">
            <a href="{{ route('home') }}" class="logo">PeerTutor</a>
            <nav class="menu">
                <a href="{{ route('home') }}" class="menu-btn active">หน้าแรก</a>
                <a href="{{ route('tutor.ranking') }}" class="menu-btn">จัดอันดับติวเตอร์</a>
                <x-nav-user-links />
                <a href="{{ route('tutor.search') }}" class="menu-btn">ค้นหาติวเตอร์</a>
                <a href="javascript:void(0)" class="menu-btn login" data-open-login>เข้าสู่ระบบ</a>
            </nav>
        </header>
        <main class="page">
            @if(session('error'))
                <div role="alert" style="padding:16px; margin-bottom:20px; border:1px solid #f5c2c7; border-radius:8px; background:#f8d7da; color:#842029;">
                    {{ session('error') }}
                </div>
            @endif
            <!-- Welcome --><section class="welcome-box">
                <h1>
                    ยินดีต้อนรับสู่
                    <b>
                        PeerTutor
                    </b>
                </h1>
                <p>ค้นหาติวเตอร์ที่เข้าใจเนื้อหาเดียวกัน แล้วเริ่มเรียนได้ทันทีด้วยประสบการณ์ที่ใกล้ตัวมากขึ้น</p>
            </section>
            <!-- All Tutors --><h2 class="section-title">ติวเตอร์ทั้งหมด</h2>
            <p class="section-sub">สำรวจและเลือกติวเตอร์ที่เหมาะกับคุณ</p>
            <div class="grid">
                @foreach($tutors as $tutor)
                    <x-tutor-card :tutor="$tutor" />
                @endforeach
            </div>
            <!-- Top Subjects --><h2 class="section-title">รายวิชายอดนิยม</h2>
            <p class="section-sub">
                รายวิชาที่นักเรียนค้นหามากที่สุดใน
                <b>
                    PeerTutor
                </b>
            </p>
            <div class="grid">
                @foreach($subjects as $subject)
                    @php
                    $title = is_array($subject) ? ($subject['title'] ?? '') : ($subject->subject_name ?? $subject->title ?? '');
                    $count = is_array($subject) ? ($subject['count'] ?? 0) : ($subject->tutors_count ?? 0);
                    $description = is_array($subject) ? ($subject['description'] ?? '') : ($subject->description ?? 'รายวิชาคุณภาพเยี่ยม');
                    $tags = is_array($subject) ? ($subject['tags'] ?? []) : ($subject->tags ?? []);
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
                        <a href="{{ route('tutor.search', ['search' => $title]) }}" class="home-card-button">
                            ดูรายวิชา
                        </a>
                    </div>
                @endforeach
            </div>
        </main>
        <x-login-modal />
        <x-register-modal />
    </body>
</html>
