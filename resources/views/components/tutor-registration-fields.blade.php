{{-- ใช้ฟอร์มติวเตอร์ชุดเดียวกันทั้งหน้าสมัครและหน้าสลับบทบาท --}}
<fieldset data-tutor-application style="border:0;padding:0;margin:0"
    @if(old('role', 'student') !== 'tutor') hidden disabled @endif>
    <x-tutor-fields />
</fieldset>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tutorForms = document.querySelectorAll('[data-tutor-application]');

        tutorForms.forEach(function (tutorFields) {
            const registerForm = tutorFields.closest('form');
            const roleInputs = registerForm.querySelectorAll('[name="role"]');

            // ซ่อนและปิดช่องติวเตอร์เมื่อเลือกนักเรียน เพื่อไม่ส่งข้อมูลส่วนนี้
            function updateTutorFields() {
                const selectedRole = registerForm.querySelector('[name="role"]:checked');
                const isTutor = selectedRole && selectedRole.value === 'tutor';
                tutorFields.hidden = !isTutor;
                tutorFields.disabled = !isTutor;
            }

            roleInputs.forEach(function (roleInput) {
                roleInput.addEventListener('change', updateTutorFields);
            });
            updateTutorFields();
        });
    });
</script>
