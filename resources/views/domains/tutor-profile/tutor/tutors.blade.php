<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติวเตอร์ทั้งหมด - PeerTutor</title>

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
        <!-- Header ส่วนหัว -->
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                    🎓 ติวเตอร์ทั้งหมดในระบบ
                </h1>
                <p style="color: #64748b; font-size: 14px; margin: 0;">
                    @if (request('search'))
                        ผลการค้นหา "<b>{{ request('search') }}</b>" พบ {{ count($tutors) }} คน
                    @else
                        เลือกติวเตอร์ที่ใช่ แล้วเริ่มเรียนกับรุ่นพี่ที่เข้าใจเนื้อหาเดียวกันได้เลย
                    @endif
                </p>
            </div>

            <button type="button" onclick="window.history.back()" class="btn" style="width: auto; padding: 10px 20px; background-color: #f1f5f9; color: #475569; font-weight: 500; cursor: pointer; border: none;">
                ← ย้อนกลับ
            </button>
        </div>

        <!-- Grid แสดงติวเตอร์ทั้งหมด -->
        <div class="grid">
            @if(isset($tutors) && count($tutors) > 0)
                @foreach($tutors as $tutor)
                    <x-tutor-card :tutor="$tutor" />
                @endforeach
            @else
                <p style="color: #94a3b8; font-size: 14px; grid-column: 1 / -1;">
                    {{ request('search') ? 'ไม่พบติวเตอร์ที่ตรงกับคำค้นหา' : 'ยังไม่มีข้อมูลติวเตอร์ในระบบ' }}
                </p>
            @endif
        </div>
    </main>


</body>
</html>
