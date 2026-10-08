@php
    $isLoginError = session('is_login_error') || ($errors->has('email') && !session('is_register_error'));
    $hasLoginError = $errors->has('email') || $errors->has('password');
    $autoOpen = session('open_login') || ($hasLoginError && $isLoginError);
@endphp

<dialog class="login-modal" id="loginModal" data-auto-open="{{ $autoOpen ? '1' : '0' }}">
    <div class="login-box">
        <button type="button" class="login-close" data-close-login aria-label="ปิด">&times;</button>

        <h2>เข้าสู่ระบบ</h2>
        <p class="login-sub">ยินดีต้อนรับกลับสู่ <b>PeerTutor</b></p>

        @if ($hasLoginError && $isLoginError)
            <p class="login-error" style="color: #ef4444; font-size: 13px; margin-bottom: 12px; text-align: center;">
                {{ $errors->first('email') ?: $errors->first('password') }}
            </p>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label for="login-email">อีเมล</label>
            <input type="email" id="login-email" name="email" value="{{ old('email') }}"
                   placeholder="email@example.com" required autocomplete="email">

            <label for="login-password">รหัสผ่าน</label>
            <input type="password" id="login-password" name="password"
                   placeholder="รหัสผ่าน" required autocomplete="current-password">

            <div class="login-row">
                <label class="login-remember">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    จดจำฉันไว้
                </label>
                <a href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a>
            </div>

            <button type="submit" class="btn login-submit">เข้าสู่ระบบ</button>
        </form>

        <p class="login-foot">
            ยังไม่มีบัญชี? 
            <a href="javascript:void(0)" id="switchToRegisterBtn" style="font-weight: 600; color: #e07a5f; text-decoration: underline; cursor: pointer;">
                สมัครสมาชิก
            </a>
        </p>
    </div>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var loginModal = document.getElementById('loginModal');
        var registerModal = document.getElementById('registerModal');
        if (!loginModal) return;

        // ปุ่มเปิด Modal Login
        document.querySelectorAll('[data-open-login]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                if (registerModal && registerModal.open) registerModal.close();
                loginModal.showModal();
            });
        });

        // ปุ่มปิด (X)
        loginModal.querySelectorAll('[data-close-login]').forEach(function (el) {
            el.addEventListener('click', function () { loginModal.close(); });
        });

        // คลิกพื้นหลังมืดเพื่อปิด
        loginModal.addEventListener('click', function (e) {
            if (e.target === loginModal) loginModal.close();
        });

        // สลับไปหน้า สมัครสมาชิก
        var switchToRegisterBtn = document.getElementById('switchToRegisterBtn');
        if (switchToRegisterBtn && registerModal) {
            switchToRegisterBtn.addEventListener('click', function (e) {
                e.preventDefault();
                loginModal.close();
                registerModal.showModal();
            });
        }

        // เปิดอัตโนมัติเมื่อมี Error จาก Login
        if (loginModal.dataset.autoOpen === '1') {
            loginModal.showModal();
        }
    });
</script>