@extends('admin.layout')
@section('title', 'จัดการรายวิชา')
@section('description', 'ดูวิชาที่เปิดสอน เพิ่มวิชาใหม่ และแก้ไขชื่อวิชาในระบบ')
@section('content')
    {{-- ส่วนค้นหาใช้ GET ส่วนเพิ่ม/แก้ไข/ลบจะส่งฟอร์มไปยัง Controller --}}
    <form class="admin-filters" method="GET">
        <label>ค้นหาวิชา<input name="search" value="{{ request('search') }}" placeholder="พิมพ์ชื่อวิชา">
        </label>
        <button class="admin-button" type="submit">ค้นหา</button>
        <a class="admin-reset" href="{{ route('admin.subjects.index') }}">ล้างตัวกรอง</a>
    </form>
    <section class="admin-panel">
        <h2>เพิ่มรายวิชา</h2>
        <form class="admin-inline-form" method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            <label>ชื่อวิชา<input name="subject_name" maxlength="45" value="{{ old('subject_name') }}"
                    placeholder="เช่น คณิตศาสตร์พื้นฐาน" required>
            </label>
            <button class="admin-button" type="submit">+ เพิ่มวิชา</button>
        </form>
    </section>
    <section class="admin-panel">
        <div class="admin-section-heading">
            <h2>รายวิชา <span>{{ $subjects->total() }} วิชา</span>
            </h2>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>รหัสวิชา</th>
                        <th>ชื่อวิชา</th>
                        <th>ติวเตอร์</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr>
                            <td>{{ $subject->subject_id }}</td>
                            <td>
                                <form id="subject-{{ $subject->subject_id }}" method="POST"
                                    action="{{ route('admin.subjects.update', $subject->subject_id) }}">
                                    @csrf
                                    {{-- HTML ส่ง PUT ตรง ๆ ไม่ได้ จึงใช้ @method บอก Laravel ว่าต้องการแก้ไข --}}
                                    @method('PUT')
                                    <label class="admin-sr-only"
                                        for="name-{{ $subject->subject_id }}">ชื่อวิชา {{ $subject->subject_name }}</label>
                                    <input id="name-{{ $subject->subject_id }}" name="subject_name"
                                        value="{{ $subject->subject_name }}" maxlength="45" required>
                                </form>
                            </td>
                            <td>{{ $subject->tutors_count }} คน</td>
                            <td>
                                <div class="admin-actions">
                                    <button class="admin-button secondary" type="submit"
                                        form="subject-{{ $subject->subject_id }}">บันทึก</button>
                                    <form action="{{ route('admin.subjects.destroy', $subject->subject_id) }}"
                                        method="POST" onsubmit="return confirm('ยืนยันลบวิชานี้ใช่ไหม?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-button danger" type="submit">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-empty">ไม่พบรายวิชาตามเงื่อนไข</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="admin-muted">วิชาที่มีข้อมูลเชื่อมกับติวเตอร์ นักเรียน นัดหมาย หรือรายการโปรดอยู่ จะไม่สามารถลบได้</p>
    </section>
    @include('admin.partials.pagination', ['items' => $subjects])
@endsection
