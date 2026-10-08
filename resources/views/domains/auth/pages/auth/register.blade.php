<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
</head>
<body class="auth-page">

    <!-- Top Logo -->
    <a href="{{ route('home') }}" class="app-logo">PeerTutor</a>

    <!-- Register Card -->
    <div class="auth-card auth-card-register">
        <h1 class="auth-title">สมัครสมาชิก</h1>
        <p class="auth-subtitle">สร้างบัญชีเพื่อใช้งาน PeerTutor</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- ชื่อ -->
            <div class="form-group">
                <label for="first_name" class="form-label">ชื่อ</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus class="form-input" placeholder="กรอกชื่อของคุณ">
                @error('first_name')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- นามสกุล -->
            <div class="form-group">
                <label for="last_name" class="form-label">นามสกุล</label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input" placeholder="กรอกนามสกุลของคุณ">
                @error('last_name')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- สมัครเป็น (Radio Buttons) -->
            <div class="form-group">
                <label class="form-label">สมัครเป็น</label>
                <div class="role-selector">
                    <label class="role-option">
                        <input type="radio" name="role" value="student" {{ old('role', 'student') == 'student' ? 'checked' : '' }} onchange="toggleTutorFields()">
                        <span>นักเรียน</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="tutor" {{ old('role') == 'tutor' ? 'checked' : '' }} onchange="toggleTutorFields()">
                        <span>ติวเตอร์</span>
                    </label>
                </div>
                @error('role')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- ช่องกรอกเพิ่มเติมสำหรับติวเตอร์ -->
            <div id="tutor-fields" style="display: none;">
                <div class="form-group">
                    <label for="line_id" class="form-label">LINE ID <span style="font-weight: normal; color: #888;">(ไม่บังคับ)</span></label>
                    <input id="line_id" type="text" name="line_id" value="{{ old('line_id') }}" class="form-input" placeholder="กรอกไอดีไลน์สำหรับติดต่อ">
                    @error('line_id')
                        <p class="alert-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="discord_id" class="form-label">Discord Username / Tag <span style="font-weight: normal; color: #888;">(ไม่บังคับ)</span></label>
                    <input id="discord_id" type="text" name="discord_id" value="{{ old('discord_id') }}" class="form-input" placeholder="เช่น username หรือ tag#1234">
                    @error('discord_id')
                        <p class="alert-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="zoom_link" class="form-label">Zoom Meeting Link <span style="font-weight: normal; color: #888;">(ไม่บังคับ)</span></label>
                    <input id="zoom_link" type="url" name="zoom_link" value="{{ old('zoom_link') }}" class="form-input" placeholder="https://zoom.us/j/...">
                    @error('zoom_link')
                        <p class="alert-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- อีเมล -->
            <div class="form-group">
                <label for="email" class="form-label">อีเมล</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="email@example.com">
                @error('email')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- รหัสผ่าน -->
            <div class="form-group">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input id="password" type="password" name="password" required class="form-input" placeholder="ตั้งรหัสผ่าน">
                @error('password')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- ยืนยันรหัสผ่าน -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input" placeholder="ยืนยันรหัสผ่านอีกครั้ง">
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                สมัครสมาชิก
            </button>
        </form>

        <div class="auth-footer">
            มีบัญชีอยู่แล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a>
        </div>
    </div>

    <script>
        function toggleTutorFields() {
            const tutorRadio = document.querySelector('input[name="role"][value="tutor"]');
            const tutorFields = document.getElementById('tutor-fields');
            if (tutorRadio && tutorFields) {
                tutorFields.style.display = tutorRadio.checked ? 'block' : 'none';
            }
        }

        // เช็กสถานะการเลือกบทบาททันทีเมื่อโหลดหน้าเว็บ (เพื่อรองรับ old input เวลา submit ไม่ผ่าน)
        document.addEventListener('DOMContentLoaded', toggleTutorFields);
    </script>

</body>
</html>