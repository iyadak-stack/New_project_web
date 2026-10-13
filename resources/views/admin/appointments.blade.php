@extends('admin.layout')
@section('title', 'ตรวจสอบนัดหมาย')
@section('description', 'ตรวจข้อมูลการจองของนักเรียนและติวเตอร์ตามวิชา วันเวลา และสถานะ')
@section('content')
    {{-- หน้านี้แสดงข้อมูลนัดหมายและค้นหาได้ ไม่มีคำสั่งเปลี่ยนการจอง --}}
    <form class="admin-filters" method="GET">
        <label>ค้นหานัดหมาย<input name="search" value="{{ request('search') }}" placeholder="ชื่อนักเรียน ติวเตอร์ หรือวิชา">
        </label>
        <label>สถานะ<select name="status">
                <option value="">ทั้งหมด</option>
                @foreach (['pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิก', 'completed' => 'เรียนเสร็จแล้ว'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>วันที่เรียน<input type="date" name="date" value="{{ request('date') }}">
        </label>
        <button class="admin-button" type="submit">ค้นหา</button>
        <a class="admin-reset" href="{{ route('admin.appointments.index') }}">ล้างตัวกรอง</a>
    </form>
    <section class="admin-panel">
        <div class="admin-section-heading">
            <h2>นัดหมาย <span>{{ $appointments->total() }} รายการ</span>
            </h2>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ผู้เรียน</th>
                        <th>ติวเตอร์ / วิชา</th>
                        <th>วันเวลา</th>
                        <th>สถานะ</th>
                        <th>รหัสนัดหมาย</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->studentProfile?->user?->first_name ?? 'ไม่พบผู้เรียน' }}
                                {{ $appointment->studentProfile?->user?->last_name }}</td>
                            <td>
                                <strong>{{ $appointment->tutorProfile?->user?->first_name ?? 'ไม่พบติวเตอร์' }}
                                    {{ $appointment->tutorProfile?->user?->last_name }}</strong>
                                <small>{{ $appointment->subject?->subject_name ?? 'ไม่พบวิชา' }}</small>
                            </td>
                            <td>{{ $appointment->start_datetime ? \Carbon\Carbon::parse($appointment->start_datetime)->format('d/m/Y') : 'ยังไม่ระบุ' }}<small>{{ $appointment->start_datetime ? \Carbon\Carbon::parse($appointment->start_datetime)->format('H:i') : '' }}
                                    –
                                    {{ $appointment->end_datetime ? \Carbon\Carbon::parse($appointment->end_datetime)->format('H:i') : '' }}</small>
                            </td>
                            <td>
                                @include('admin.partials.status', ['status' => $appointment->status])</td>
                            <td>{{ $appointment->Appointment_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">ไม่พบนัดหมายตามเงื่อนไข</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @include('admin.partials.pagination', ['items' => $appointments])
@endsection
