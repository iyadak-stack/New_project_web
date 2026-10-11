<nav class="admin-nav px-4 py-3 d-flex gap-4 align-items-center flex-wrap">
    <a class="fw-bold" href="{{ route('admin.dashboard') }}">PeerTutor Admin</a>
    <a href="{{ route('admin.reports.index') }}">รายงาน</a>
    <a href="{{ route('admin.users.index') }}">ผู้ใช้</a>
    <a href="{{ route('admin.tutors.index') }}">ตรวจสอบติวเตอร์ ({{ \App\Domains\TutorProfile\Models\TutorProfile::where('approval_status','pending')->count() }})</a>
    @auth
        <span class="text-white ms-auto">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">ออกจากระบบ</button>
        </form>
    @else
        <a class="ms-auto" href="{{ route('login') }}">เข้าสู่ระบบ</a>
    @endauth
</nav>
