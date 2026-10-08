<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตรหัสผ่าน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
</head>
<body class="auth-page">

    <!-- Top Logo -->
    <a href="{{ route('home') }}" class="app-logo">PeerTutor</a>

    <!-- Auth Card -->
    <div class="auth-card">
        <h1 class="auth-title">รีเซ็ตรหัสผ่าน</h1>
        <div class="step-indicator">ขั้นตอนที่ 2 จาก 2</div>
        <p class="auth-subtitle">กรอกรหัสผ่านใหม่ของคุณด้านล่าง</p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert-status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">อีเมล</label>
                <input id="email" type="email" name="email" value="{{ request('email') }}" required readonly class="form-input" placeholder="iyada.k@kkumail.com">
                @error('email')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">รหัสผ่านใหม่</label>
                <input id="password" type="password" name="password" required autofocus class="form-input" placeholder="กรอกรหัสผ่านใหม่">
                @error('password')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
            </div>

            <button type="submit" class="btn-primary">
                บันทึกรหัสผ่านใหม่
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">กลับไปเข้าสู่ระบบ</a>
        </div>
    </div>

</body>
</html>