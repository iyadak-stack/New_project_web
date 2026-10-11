@php
    $hasRegisterError = $errors->has('first_name') || $errors->has('last_name') || $errors->has('password') || $errors->has('password_confirmation') || ($errors->has('email') && session('is_register_error'));
    $autoOpen = session('open_register') || $hasRegisterError || (session('is_register_error') && $errors->any());
@endphp
<dialog class="login-modal register-modal" id="registerModal" data-auto-open="{{ $autoOpen ? '1' : '0' }}">
    <div class="login-box register-box">
        <!-- ปุ่มปิดป๊อปอัพ (X) --><button type="button" class="login-close close-btn" data-close-register aria-label="ปิด">&times;</button>
        <div class="modal-header">
            <h2>สมัครสมาชิก</h2>
            <p>สร้างบัญชีเพื่อเริ่มต้นใช้งาน PeerTutor</p>
        </div>
        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="register-form">
            @csrf
            @if($errors->any() && session('is_register_error'))
                <div class="alert-error" role="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <!-- ชื่อ --><div class="form-group">
                <label>
                    ชื่อ
                </label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="กรอกชื่อ">
                @error('first_name')
                <span class="error-text" style="color: #ef4444; font-size: 11px; display: block; margin-top: 2px;">{{ $message }}</span>
                @enderror
            </div>
            <!-- นามสกุล --><div class="form-group">
                <label>
                    นามสกุล
                </label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="กรอกนามสกุล">
                @error('last_name')
                <span class="error-text" style="color: #ef4444; font-size: 11px; display: block; margin-top: 2px;">{{ $message }}</span>
                @enderror
            </div>
            <!-- สมัครเป็น --><div class="form-group">
                <label>
                    สมัครเป็น
                </label>
                <div class="role-options" style="display: flex; gap: 16px; margin-top: 6px; margin-bottom: 6px;">
                    <label class="role-radio" style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; color: #374151;">
                        <input type="radio" name="role" value="student" {{ old('role', 'student') == 'student' ? 'checked' : '' }} style="accent-color: #007bff;">
                        <span>นักเรียน</span>
                    </label>
                    <label class="role-radio" style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; color: #374151;">
                        <input type="radio" name="role" value="tutor" {{ old('role') == 'tutor' ? 'checked' : '' }} style="accent-color: #007bff;">
                        <span>ติวเตอร์</span>
                    </label>
                </div>
                @error('role')
                <span class="error-text" style="color: #ef4444; font-size: 11px; display: block; margin-top: 2px;">{{ $message }}</span>
                @enderror
            </div>
            <!-- ข้อมูลเพิ่มเติมสำหรับติวเตอร์ (ซ่อน/แสดงตามบทบาท) --><x-tutor-registration-fields />
            <!-- อีเมล --><div class="form-group">
                <label>
                    อีเมล
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
                @error('email')
                @if(!session('is_login_error'))
                    <span class="error-text" style="color: #ef4444; font-size: 11px; display: block; margin-top: 2px;">{{ $message }}</span>
                @endif
                @enderror
            </div>
            <!-- รหัสผ่าน --><div class="form-group">
                <label>
                    รหัสผ่าน
                </label>
                <input type="password" name="password" required placeholder="ตั้งรหัสผ่านอย่างน้อย 8 ตัวอักษร">
                @error('password')
                @if(!session('is_login_error'))
                    <span class="error-text" style="color: #ef4444; font-size: 11px; display: block; margin-top: 2px;">
                        {{ $message == 'The password field must be at least 8 characters.' ? 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร' : $message }}
                    </span>
                @endif
                @enderror
            </div>
            <!-- ยืนยันรหัสผ่าน --><div class="form-group">
                <label>
                    ยืนยันรหัสผ่าน
                </label>
                <input type="password" name="password_confirmation" required placeholder="ยืนยันรหัสผ่านอีกครั้ง">
            </div>
            <!-- ปุ่มสมัครสมาชิก --><button type="submit" class="btn-submit" style="width: 100%; margin-top: 12px; background-color: #e07a5f; color: #ffffff; border: none; padding: 10px; border-radius: 9999px; font-size: 13px; font-weight: 600; cursor: pointer;">
                สมัครสมาชิก
            </button>
        </form>
        <!-- สลับไป Login --><div class="modal-footer" style="text-align: center; margin-top: 16px; font-size: 13px; color: #6b7280;">
            มีบัญชีอยู่แล้ว?
            <a href="javascript:void(0)" id="switchToLoginBtn" style="font-weight: 600; color: #e07a5f; text-decoration: underline; margin-left: 4px; cursor: pointer;">เข้าสู่ระบบ</a>
        </div>
    </div>
</dialog>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var registerModal = document.getElementById('registerModal');
        var loginModal = document.getElementById('loginModal');
        if (!registerModal) return;

        // สลับแสดง/ซ่อนฟิลด์สำหรับติวเตอร์
        function toggleModalTutorFields() {
            var tutorRadio = registerModal.querySelector('input[name="role"][value="tutor"]');
            var tutorFields = document.getElementById('modal-tutor-fields');
            if (tutorRadio && tutorFields) {
                tutorFields.style.display = tutorRadio.checked ? 'block' : 'none';
            }
        }

        // ผูก event listener ให้ปุ่มเลือกบทบาท
        registerModal.querySelectorAll('input[name="role"]').forEach(function (radio) {
            radio.addEventListener('change', toggleModalTutorFields);
        });

        // ตรวจสอบบทบาทแรกเริ่มตอนโหลดหน้าเว็บ
        toggleModalTutorFields();

        // ปุ่มเปิด Modal Register
        document.querySelectorAll('[data-open-register]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                if (loginModal && loginModal.open) loginModal.close();
                registerModal.showModal();
            });
        });

        // ปุ่มปิด (X)
        registerModal.querySelectorAll('[data-close-register]').forEach(function (el) {
            el.addEventListener('click', function () { registerModal.close(); });
        });

        // คลิกพื้นหลังมืดเพื่อปิด
        registerModal.addEventListener('click', function (e) {
            if (e.target === registerModal) registerModal.close();
        });

        // สลับไปหน้า เข้าสู่ระบบ
        var switchToLoginBtn = document.getElementById('switchToLoginBtn');
        if (switchToLoginBtn && loginModal) {
            switchToLoginBtn.addEventListener('click', function (e) {
                e.preventDefault();
                registerModal.close();
                loginModal.showModal();
            });
        }

        // เปิดอัตโนมัติเมื่อมี Error จาก Register
        if (registerModal.dataset.autoOpen === '1') {
            registerModal.showModal();
        }
    });
</script>
