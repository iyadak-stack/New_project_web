@once
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // บางหน้ามีเมนูบัญชีมากกว่าหนึ่งจุด จึงลงทะเบียนเหตุการณ์เพียงครั้งเดียว
        if (!window.logoutConfirmReady) {
            window.logoutConfirmReady = true;
            var logoutConfirmOpen = false;

            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                // ดักเฉพาะฟอร์มออกจากระบบ ไม่รบกวนฟอร์มอื่นในเว็บ
                if (form.action !== @json(route('logout')) || form.method.toLowerCase() !== 'post') {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();
                if (logoutConfirmOpen) {
                    return;
                }

                // หากโหลด SweetAlert ไม่สำเร็จ ยังต้องให้ผู้ใช้ยืนยันก่อน
                if (!window.Swal) {
                    if (window.confirm('ต้องการออกจากระบบจริง ๆ ใช่ไหม?')) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                    return;
                }

                logoutConfirmOpen = true;
                Swal.fire({
                    title: 'ออกจากระบบ?',
                    text: 'ต้องการออกจากระบบจริง ๆ ใช่ไหม?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ออกจากระบบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#ed805b',
                    cancelButtonColor: '#6b7280',
                    focusCancel: true
                }).then(function (result) {
                    logoutConfirmOpen = false;
                    if (result.isConfirmed) {
                        // ส่งฟอร์ม POST เดิมพร้อม CSRF หลังผู้ใช้ยืนยันแล้วเท่านั้น
                        HTMLFormElement.prototype.submit.call(form);
                    }
                });
            }, true);
        }
    </script>
@endonce
