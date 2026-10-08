<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์นักเรียน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}?v={{ time() }}">
</head>
<body>

@php
    $user = auth()->user();
    $avatarUrl = $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&color=e07a5f&background=fdeee8';
@endphp

    <!-- แถบเมนูด้านบน (แบบเดียวกับหน้าหลัก) -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>

        <nav class="menu">
            <a href="{{ route('home') }}" class="menu-btn">หน้าแรก</a>
            <a href="{{ route('tutor.ranking') }}" class="menu-btn">จัดอันดับติวเตอร์</a>
            <a href="{{ route('notifications.index') }}" class="menu-btn">แจ้งเตือน</a>
        </nav>
    </header>

    <main class="pe-page">
        <div class="pe-card">

            <!-- หัวข้อ -->
            <h1 class="pe-title">แก้ไขโปรไฟล์</h1>
            <p class="pe-sub">อัปเดตข้อมูลประวัติส่วนตัวและรูปโปรไฟล์ของคุณ</p>

            @if ($errors->any())
                <div class="pe-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ฟอร์มเดิม: route, enctype, csrf และชื่อช่องทุกอย่างเหมือนเดิม --}}
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- รูปโปรไฟล์ -->
                <div class="pe-avatar-wrap">
                    <div class="pe-avatar">
                        <img id="avatar-preview" src="{{ $avatarUrl }}" alt="Profile Image">
                    </div>

                    <label for="avatar" class="pe-avatar-btn">เปลี่ยนรูปโปรไฟล์</label>
                    <input type="file" id="avatar" name="avatar" style="display: none;" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- ข้อมูลส่วนตัว -->
                <div class="pe-section">
                    <h2>ข้อมูลส่วนตัว</h2>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="first_name">ชื่อ</label>
                            <input type="text" id="first_name" name="first_name"
                                   value="{{ old('first_name', $user->first_name) }}">
                        </div>
                        <div class="pe-field">
                            <label for="last_name">นามสกุล</label>
                            <input type="text" id="last_name" name="last_name"
                                   value="{{ old('last_name', $user->last_name) }}">
                        </div>
                    </div>

                    <div class="pe-field">
                        <label for="bio">ประวัติส่วนตัว (Bio)</label>
                        <textarea id="bio" name="bio" rows="3"
                                  placeholder="ระบุเกี่ยวกับตัวคุณ เช่น สไตล์การเรียน หรือเป้าหมายที่ต้องการเรียนรู้">{{ old('bio', $studentProfile->bio ?? '') }}</textarea>
                    </div>
                </div>

                <!-- ช่องทางการติดต่อ -->
                <div class="pe-section">
                    <h2>ช่องทางการติดต่อ</h2>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" placeholder="เช่น mylineid"
                                   value="{{ old('line_id', $studentProfile->line_id ?? '') }}">
                        </div>
                        <div class="pe-field">
                            <label for="discord_id">Discord ID</label>
                            <input type="text" id="discord_id" name="discord_id" placeholder="เช่น username#1234"
                                   value="{{ old('discord_id', $studentProfile->discord_id ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- ปุ่มกด -->
                <div class="pe-actions">
                    <button type="submit" class="pe-save">บันทึกข้อมูล</button>
                    <!-- เปลี่ยนจาก route('tutor.profile') เป็น route('home') -->
                    <a href="{{ route('home') }}" class="pe-cancel">ยกเลิก</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // แสดงตัวอย่างรูปทันทีที่เลือกไฟล์ (โค้ดเดิม)
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => document.getElementById('avatar-preview').src = reader.result;
            reader.readAsDataURL(file);
        }
    </script>

</body>
</html>