@auth
    <a href="{{ route('tutor.favorites') }}" class="menu-btn {{ request()->routeIs('tutor.favorites') ? 'active' : '' }}">
        รายการโปรด
    </a>
    <a href="{{ route('appointments.index') }}" class="menu-btn {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
        นัดหมายของฉัน
    </a>
    @if(auth()->user()->current_role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="menu-btn {{ request()->routeIs('admin.*') ? 'active' : '' }}">
            Admin
        </a>
    @endif
@endauth
