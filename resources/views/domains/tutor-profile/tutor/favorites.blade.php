<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการโปรด - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/favorites.css') }}">
</head>
<body class="favorites-body">
    {{-- ใช้เมนูด้านบนเดิมของระบบ โดยไม่มี Sidebar --}}
    <x-navbar />

    <main class="favorites-page">
        {{-- Banner มีเพียงหัวข้อและคำอธิบาย ตามขนาดที่กำหนด --}}
        <header class="favorites-banner">
            <h1>รายการโปรด</h1>
            <p>รายชื่อติวเตอร์ที่คุณบันทึกไว้ เพื่อให้คุณกลับมาเรียนได้ง่ายขึ้น</p>
        </header>

        @if (session('success'))
            <div class="favorites-message" role="status">{{ session('success') }}</div>
        @endif

        <section aria-labelledby="favorites-heading">
            <div class="favorites-summary">
                <div>
                    <h2 id="favorites-heading">ติวเตอร์ที่คุณชื่นชอบ <span>{{ $favorites->count() }} คน</span></h2>
                    <p>เลือกดูโปรไฟล์ เพื่อรู้จักติวเตอร์และวิชาที่สอนเพิ่มเติม</p>
                </div>
                <span class="favorites-order">เรียงจากรายการที่บันทึกล่าสุด</span>
            </div>

            {{-- forelse แสดงการ์ดเมื่อมีข้อมูล และแสดงข้อความเมื่อไม่มีข้อมูล --}}
            <div class="favorites-list">
                @forelse ($favorites as $favorite)
                    @php
                        $tutor = $favorite->tutorProfile;
                        $user = $tutor->user;
                    @endphp
                    <article class="favorite-card">
                        <div class="favorite-details">
                            {{-- ถ้ายังไม่อัปโหลดรูป ใช้ตัวอักษรแรกของชื่อแทน --}}
                            <div class="favorite-avatar">
                                @if ($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="รูปโปรไฟล์ของ {{ $user->first_name }}" loading="lazy">
                                @else
                                    <span aria-label="ยังไม่มีรูปโปรไฟล์">{{ mb_substr($user->first_name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="favorite-info">
                                <h3>พี่{{ $user->first_name }} {{ $user->last_name }}</h3>
                                <div class="favorite-subjects">
                                    @forelse ($tutor->subjects as $subject)
                                        <span>{{ $subject->subject_name }}</span>
                                    @empty
                                        <span>ยังไม่ระบุวิชาที่สอน</span>
                                    @endforelse
                                </div>
                                <p class="favorite-rating">
                                    {{-- ใช้คะแนนจริง ไม่ใส่คะแนน 5.0 ให้ติวเตอร์ที่ยังไม่มีรีวิว --}}
                                    @if ($tutor->average_rating > 0)
                                        <span aria-hidden="true">★</span> {{ number_format($tutor->average_rating, 1) }} / 5.0
                                    @else
                                        ยังไม่มีคะแนนรีวิว
                                    @endif
                                </p>
                                <p class="favorite-bio">{{ $tutor->bio ?: 'ติวเตอร์ยังไม่ได้เพิ่มข้อความแนะนำตัว' }}</p>
                                <div class="favorite-tags">
                                    @if ($tutor->teaching_mode === 'online')
                                        <span>สอนออนไลน์</span>
                                    @elseif ($tutor->teaching_mode === 'onsite')
                                        <span>สอนแบบนัดเจอ</span>
                                    @elseif ($tutor->teaching_mode === 'both')
                                        <span>สอนออนไลน์</span><span>สอนแบบนัดเจอ</span>
                                    @else
                                        <span>ยังไม่ระบุรูปแบบการสอน</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="favorite-side">
                            {{-- ออกแบบประสบการณ์เป็นตัวเลขเด่น แยกจากข้อความแนะนำตัว --}}
                            <div class="favorite-experience">
                                <span class="experience-label">ประสบการณ์การติว</span>
                                @if ($tutor->experience_years > 0)
                                    <p><strong>{{ $tutor->experience_years }}</strong> ปี</p>
                                    <span class="experience-note">ประสบการณ์ที่ติวเตอร์ระบุในโปรไฟล์</span>
                                @else
                                    <p class="experience-new">ติวเตอร์ใหม่</p>
                                    <span class="experience-note">เริ่มต้นแบ่งปันความรู้ไปด้วยกัน</span>
                                @endif
                            </div>
                            {{-- ส่ง tutor_id ไปยังหน้าโปรไฟล์ของติวเตอร์คนนี้ มีปุ่มเดียวต่อการ์ด --}}
                            <a class="favorite-profile-link" href="{{ route('tutor.show', $tutor->tutor_id) }}" aria-label="ดูโปรไฟล์ของ {{ $user->first_name }} {{ $user->last_name }}">ดูโปรไฟล์ <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @empty
                    <div class="favorites-empty">
                        <span class="empty-heart" aria-hidden="true">♡</span>
                        <h3>ยังไม่มีติวเตอร์ในรายการโปรด</h3>
                        <p>กดเพิ่มรายการโปรดจากหน้าโปรไฟล์ติวเตอร์ที่สนใจ แล้วกลับมาดูได้ที่นี่</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
