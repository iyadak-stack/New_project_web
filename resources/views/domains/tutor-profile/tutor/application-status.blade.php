<x-tutor-page title="สถานะใบสมัครติวเตอร์">
    <section class="card p-4" style="max-width:720px">
        @if($profile->approval_status === 'approved')
            <h2 class="h4 text-success">ใบสมัครได้รับอนุมัติแล้ว</h2>
            <p>คุณสามารถเข้าใช้งานระบบติวเตอร์ได้แล้ว</p>
            <form method="POST" action="{{ route('role.switch') }}">
                @csrf
                <input type="hidden" name="role" value="tutor">
                <button class="btn btn-success">เข้าสู่หน้าติวเตอร์</button>
            </form>
        @elseif($profile->approval_status === 'rejected')
            <h2 class="h4 text-danger">ใบสมัครยังไม่ผ่านการตรวจสอบ</h2>
            <p>เหตุผล: {{ $profile->reject_reason }}</p>
            <a class="btn btn-primary" href="{{ route('tutor.onboarding') }}">แก้ไขข้อมูลและส่งใหม่</a>
        @else
            <h2 class="h4">รอแอดมินตรวจสอบ</h2>
            <p>ส่งข้อมูลแล้ว ระบบจะแจ้งผลเมื่อแอดมินตรวจสอบเสร็จ ระหว่างนี้ยังไม่สามารถเปิดสอนหรือรับคำขอจองได้</p>
            <a href="{{ route('tutor.onboarding') }}">ตรวจและแก้ไขข้อมูลใบสมัคร</a>
        @endif
        <hr>
        <form method="POST" action="{{ route('role.switch') }}">
            @csrf
            <input type="hidden" name="role" value="student">
            <button class="btn btn-outline-secondary">ใช้งานเป็นนักเรียน</button>
        </form>
    </section>
</x-tutor-page>
