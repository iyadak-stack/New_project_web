<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
</head>
<body class="auth-page">

    <!-- Top Logo -->
    <a href="{{ route('home') }}" class="app-logo">PeerTutor</a>

    <!-- Auth Card -->
    <div class="auth-card">
        <h1 class="auth-title">ลืมรหัสผ่าน</h1>
        <div class="step-indicator">ขั้นตอนที่ 1 จาก 2</div>
        <p class="auth-subtitle">กรอกอีเมลที่ใช้สมัครของคุณ</p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert-status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">อีเมล</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-input" placeholder="iyada.k@kkumail.com">
                @error('email')
                    <p class="alert-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                ต่อไป
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">กลับไปเข้าสู่ระบบ</a>
        </div>
    </div>

</body>
</html>