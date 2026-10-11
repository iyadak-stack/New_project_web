<x-tutor-page :title="request()->routeIs('tutor.onboarding') ? 'สมัครเป็นติวเตอร์' : 'แก้ไขข้อมูลติวเตอร์'">
    <form method="POST" enctype="multipart/form-data" action="{{ request()->routeIs('tutor.onboarding') ? route('tutor.onboarding.store') : route('tutor.profile.update') }}" style="max-width:720px">
        @csrf
        <x-tutor-fields :profile="$tutorProfile" :contact="$contact" />
        @if($tutorProfile->approval_status !== 'approved')
            <p>เมื่อส่งข้อมูลแล้ว แอดมินจะตรวจสอบก่อนเปิดใช้งานระบบติวเตอร์</p>
        @endif
        <button class="btn btn-primary">{{ $tutorProfile->approval_status === 'approved' ? 'บันทึกข้อมูล' : 'ส่งข้อมูลให้แอดมินตรวจสอบ' }}</button>
        <a class="btn btn-light" href="{{ route('home') }}">กลับ</a>
    </form>
</x-tutor-page>
