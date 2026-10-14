@extends('admin.layout')
@section('title', 'จัดการผู้ใช้')
@section('description', 'ค้นหาบัญชี ดูโปรไฟล์ และจัดการสถานะการใช้งานของนักเรียนและติวเตอร์')
@section('content')
    {{-- ฟอร์ม GET ส่งตัวกรองไปยังเมธอด users() ใน AdminController --}}
    <form class="admin-filters" method="GET">
        <label>ค้นหาผู้ใช้<input name="search" value="{{ request('search') }}" placeholder="ชื่อ นามสกุล หรืออีเมล">
        </label>
        <label>ประเภท<select name="role">
                <option value="">ทั้งหมด</option>
                @foreach (['student' => 'นักเรียน', 'tutor' => 'ติวเตอร์', 'admin' => 'แอดมิน'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>สถานะ<select name="active">
                <option value="">ทั้งหมด</option>
                <option value="1" @selected(request('active') === '1')>ใช้งานอยู่</option>
                <option value="0" @selected(request('active') === '0')>พักบัญชี</option>
            </select>
        </label>
        <button class="admin-button" type="submit">ค้นหา</button>
        <a class="admin-reset" href="{{ route('admin.users.index') }}">ล้างตัวกรอง</a>
    </form>
    <section class="admin-panel">
        <div class="admin-section-heading">
            <h2>บัญชีผู้ใช้ <span>{{ $users->total() }} คน</span>
            </h2>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ชื่อ / อีเมล</th>
                        <th>ประเภท</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $member)
                        <tr>
                            <td>
                                <strong>{{ $member->first_name }} {{ $member->last_name }}</strong>
                                <small>{{ $member->email }}</small>
                            </td>
                            <td>
                                @if ($member->role === 'admin' || $member->current_role === 'admin')
                                    แอดมิน
                                @elseif ($member->tutorProfile)
                                    ติวเตอร์ / นักเรียน
                                @else
                                    นักเรียน
                                @endif
                            </td>
                            <td>
                                <span
                                    class="admin-badge {{ $member->is_active ? 'green' : 'red' }}">{{ $member->is_active ? 'ใช้งานอยู่' : 'พักบัญชี' }}</span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a class="admin-button secondary"
                                        href="{{ route('admin.users.show', $member->user_id) }}">ดูโปรไฟล์</a>
                                    @if ($member->role !== 'admin' && $member->current_role !== 'admin' && $member->user_id !== auth()->id())
                                        {{-- ส่งเฉพาะสถานะบัญชี และถามยืนยันก่อนส่งฟอร์ม --}}
                                        <form method="POST" action="{{ route('admin.users.status', $member->user_id) }}"
                                            onsubmit="return confirm('ยืนยันเปลี่ยนสถานะบัญชีนี้ใช่ไหม?')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active"
                                                value="{{ $member->is_active ? '0' : '1' }}">
                                            <button class="admin-button {{ $member->is_active ? 'danger' : 'secondary' }}"
                                                type="submit">{{ $member->is_active ? 'พักบัญชี' : 'เปิดใช้งาน' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-empty">ไม่พบผู้ใช้ตามเงื่อนไข</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @include('admin.partials.pagination', ['items' => $users])
@endsection
