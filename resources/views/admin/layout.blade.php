<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - PeerTutor Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- แยก CSS แอดมิน ไม่โหลด home.css เพื่อไม่เปลี่ยนสีฝั่งนักเรียน/ติวเตอร์ --}}
    <link rel="stylesheet" href="{{ asset('CSS/admin.css') }}">
</head>

<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="admin-logo" href="{{ route('admin.dashboard') }}">PeerTutor<span>ADMIN</span>
        </a>
        <p class="admin-menu-label">จัดการเว็บ</p>
                {{-- เมนูแอดมิน 6 หน้า: routeIs ใช้เช็กว่าขณะนี้อยู่หน้าไหน --}}
        <nav aria-label="เมนูแอดมิน">
            <a href="{{ route('admin.tutor-applications.index') }}" class="admin-nav-link {{ request()->routeIs('admin.tutor-applications.*') ? 'is-active' : '' }}">คำขอติวเตอร์</a>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <span class="nav-number">01</span> ภาพรวม
            </a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                <span class="nav-number">02</span> จัดการผู้ใช้
            </a>
            <a href="{{ route('admin.reports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.*', 'admin.reasons.*') ? 'is-active' : '' }}">
                <span class="nav-number">03</span> จัดการรายงาน
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="admin-nav-link {{ request()->routeIs('admin.subjects.*') ? 'is-active' : '' }}">
                <span class="nav-number">04</span> จัดการรายวิชา
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}">
                <span class="nav-number">05</span> ตรวจสอบรีวิว
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="admin-nav-link {{ request()->routeIs('admin.appointments.*') ? 'is-active' : '' }}">
                <span class="nav-number">06</span> ตรวจสอบนัดหมาย
            </a>
        </nav> <div class="admin-sidebar-bottom">
            <p>ผู้ดูแลระบบ<br>
                <strong>{{ auth()->user()->first_name }}</strong>
            </p>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="admin-button secondary" type="submit">ออกจากระบบ</button>
            </form>
        </div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar">
            <span>พื้นที่ผู้ดูแลระบบ</span>
            <span class="admin-account">
                <span
                    class="admin-avatar">{{ mb_substr(auth()->user()->first_name, 0, 1) }}</span>{{ auth()->user()->first_name }}</span>
        </header>
        <main class="admin-page">
            <header class="admin-banner">
                <span class="admin-eyebrow">PEERTUTOR / ADMIN</span>
                <h1>@yield('title')</h1>
                <p>@yield('description')</p>
            </header>

            @if (session('success'))
                {{-- ข้อความหลัง Controller บันทึกข้อมูลสำเร็จ --}}
                <div class="admin-alert success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                {{-- ข้อผิดพลาดจากการ validate แสดงให้ผู้ใช้แก้ไขฟอร์ม --}}
                <div class="admin-alert error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    <x-logout-confirm />
</body>

</html>
