<nav class="peer-navbar">
    <div class="peer-navbar-left">
        <a href="{{ route('home') }}" class="peer-logo {{ request()->routeIs('home') ? 'active' : '' }}">
            PeerTutor
        </a>
        <a href="{{ route('home') }}" class="peer-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            หน้าแรก
        </a>
        <a href="{{ route('tutor.search') }}" class="peer-nav-link {{ request()->routeIs('tutor.search') ? 'active' : '' }}">
            ค้นหาติวเตอร์/วิชา
        </a>
        <a href="{{ route('tutor.ranking') }}" class="peer-nav-link {{ request()->routeIs('tutor.ranking') ? 'active' : '' }}">
            จัดอันดับติวเตอร์
        </a>
        @auth
            <a href="{{ route('tutor.favorites') }}" class="peer-nav-link {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">
                รายการโปรด
            </a>
            <a href="{{ route('appointments.index') }}" class="peer-nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                นัดหมายของฉัน
            </a>
            @if(auth()->user()->current_role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="peer-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    Admin
                </a>
            @endif
        @endauth
        @php($unreadNotificationCount = \App\Domains\Booking\Models\Notification::query()->where('Users_user_id', auth()->id())->where('is_read', false)->count())
        <a href="{{ route('notifications.index') }}" class="peer-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            🔔
            @if($unreadNotificationCount > 0)
                <span class="badge bg-danger">{{ $unreadNotificationCount }}</span>
            @endif
        </a>
    </div>
    <div class="peer-navbar-right">
        @auth
            <span class="peer-role">สถานะ: {{ auth()->user()->current_role === 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }}</span>
            <form action="{{ route('role.switch') }}" method="POST" class="peer-role-form">
                @csrf
                <input
                    type="hidden"
                    name="role"
                    value="{{ auth()->user()->current_role === 'tutor' ? 'student' : 'tutor' }}"
                >
                <button type="submit" class="peer-switch">สลับเป็น{{ auth()->user()->current_role === 'tutor' ? 'นักเรียน' : 'ติวเตอร์' }}</button>
            </form>
            <a href="{{ route('profile.edit') }}"
               class="peer-nav-link {{ request()->routeIs('profile.edit', 'appearance.edit', 'security.edit') ? 'active' : '' }}">
                ตั้งค่าบัญชี
            </a>
        @else
            <a href="{{ route('login') }}" class="peer-nav-link">เข้าสู่ระบบ</a>
        @endauth
    </div>
</nav>
