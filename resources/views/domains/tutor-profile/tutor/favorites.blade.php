<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>รายการโปรด - PeerTutor</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
        <style>.favorites-page{max-width:1150px;margin:40px auto;padding:0 20px}.favorite-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:20px}.favorite-card{background:#fff;border:1px solid #eee;border-radius:18px;padding:22px;box-shadow:0 5px 18px #00000008}.favorite-card h2{margin:0 0 8px}.favorite-card p{color:#555;line-height:1.7}.favorite-actions{display:flex;gap:10px;align-items:center}.favorite-actions a,.favorite-actions button{border:0;border-radius:9px;padding:9px 13px;text-decoration:none;cursor:pointer}.favorite-actions a{background:#e07a5f;color:#fff}.favorite-actions button{background:#f1f5f9;color:#334155}</style>
        <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}">
    </head>
    <body>
        <x-home-navbar />
        <main class="favorites-page">
            <h1>รายการโปรด</h1>
            <p>ติวเตอร์ที่คุณบันทึกไว้</p>
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($favorites->isNotEmpty())
                <div class="favorite-grid">
                    @foreach($favorites as $favorite)
                        @php($tutor = $favorite->tutorProfile)
                        @if($tutor)
                            <article class="favorite-card">
                                <h2>{{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }}</h2>
                                <p>
                                    ⭐ {{ number_format($tutor->average_rating, 1) }} · ประสบการณ์ {{ $tutor->experience_years }} ปี
                                    <br>
                                    รูปแบบ: {{ ['online' => 'ออนไลน์', 'onsite' => 'พบกัน', 'both' => 'ออนไลน์/พบกัน'][$tutor->teaching_mode] ?? $tutor->teaching_mode }}
                                    <br>
                                    {{ $tutor->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว' }}
                                </p>
                                <div class="favorite-actions">
                                    <a href="{{ route('tutor.show', $tutor) }}">ดูโปรไฟล์ / จอง</a>
                                    <form action="{{ route('tutor.favorite.destroy', $tutor) }}" method="POST">
                                        @csrf
                                        <button type="submit">ลบจากรายการโปรด</button>
                                    </form>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="favorite-card">
                    <h2>ยังไม่มีติวเตอร์ในรายการโปรด</h2>
                    <p>ไปที่โปรไฟล์ติวเตอร์แล้วกดหัวใจเพื่อบันทึกไว้ที่นี่</p>
                    <a href="{{ route('tutor.search') }}">ค้นหาติวเตอร์</a>
                </div>
            @endif
        </main>
    </body>
</html>
