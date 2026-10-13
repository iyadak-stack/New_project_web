@once
    {{-- ใช้สี ขนาด และรูปแบบ navbar เดิมร่วมกันทุกหน้าสาธารณะ --}}
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/guest-auth.css') }}">
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">PeerTutor</a>
        <form action="{{ route('tutor.search') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาวิชา ติวเตอร์" aria-label="ค้นหาวิชา ติวเตอร์">
            <button type="submit">ค้นหา</button>
        </form>
        <nav class="menu" aria-label="เมนูผู้เยี่ยมชม">
            <a href="{{ route('home') }}" class="menu-btn {{ request()->routeIs('home') ? 'active' : '' }}">หน้าแรก</a>
            <a href="{{ route('subjects.index') }}" class="menu-btn {{ request()->routeIs('subjects.*') ? 'active' : '' }}">รายวิชาทั้งหมด</a>
            <a href="{{ route('tutor.search') }}" class="menu-btn {{ request()->routeIs('tutor.search', 'tutor.show', 'student.tutors.*', 'tutor.tutors', 'tutor.ranking') ? 'active' : '' }}">ติวเตอร์ทั้งหมด</a>
            <a href="{{ route('login') }}" class="menu-btn login" data-open-login>เข้าสู่ระบบ</a>
            <a href="{{ route('register') }}" class="menu-btn guest-register" data-open-register>สมัครสมาชิก</a>
        </nav>
    </header>

    {{-- เรียก popup เดิมเพียงชุดเดียว ป้องกัน id ซ้ำในหน้า --}}
    <x-login-modal />
    <x-register-modal />
@endonce
