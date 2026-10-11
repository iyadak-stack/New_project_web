<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PeerTutor - หน้านักเรียน</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    </head>
    <body>
        <!-- Navbar --><x-home-navbar />
        <main class="page">
            <!-- Welcome Section (ปรับให้อ่านชื่อผู้ใช้ตรงๆ ไม่ต้องเช็ก Auth::check และระยะห่างตรงกับติวเตอร์) --><section class="welcome-box">
                <h1>
                    ยินดีต้อนรับคุณ
                    <b style="color:#e07a5f;">
                        {{ Auth::user()->first_name }}
                    </b>
                    สู่
                    <b>
                        PeerTutor
                    </b>
                </h1>
                <p>ค้นหาติวเตอร์ที่เข้าใจเนื้อหาเดียวกัน แล้วเริ่มเรียนได้ทันทีด้วยประสบการณ์ที่ใกล้ตัวมากขึ้น</p>
            </section>
            <!-- Top Tutors --><h2 class="section-title">ติวเตอร์ยอดนิยม</h2>
            <p class="section-sub">สำรวจติวเตอร์ที่มีคะแนนสูงสุด</p>
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
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggleProfileBtn');
            const dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
    </body>
</html>
