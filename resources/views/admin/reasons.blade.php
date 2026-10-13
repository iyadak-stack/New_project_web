@extends('admin.layout')
@section('title', 'ประเภทการรายงาน')
@section('description', 'จัดการหัวข้อที่ผู้ใช้เลือกเมื่อส่งรายงานจากโปรไฟล์ติวเตอร์')
@section('content')
    {{-- รายการประเภทมาจาก report_reasons ชื่อที่แก้จะใช้ในฟอร์มรายงานของผู้ใช้ด้วย --}}
    <div class="admin-subnav">
        <a href="{{ route('admin.reports.index') }}">รายงานจากผู้ใช้</a>
        <a class="is-active" href="{{ route('admin.reasons.index') }}">ประเภทการรายงาน</a>
    </div>
    <section class="admin-panel">
        <h2>เพิ่มประเภทการรายงาน</h2>
        <form class="admin-inline-form" action="{{ route('admin.reasons.store') }}" method="POST">
            @csrf
            <label>ชื่อประเภท<input name="reason_name" value="{{ old('reason_name') }}" maxlength="45" required>
            </label>
            <button class="admin-button" type="submit">+ เพิ่มประเภท</button>
        </form>
    </section>
    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ประเภท</th>
                        <th>รายงานที่ใช้</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reasons as $reason)
                        <tr>
                            <td>
                                <form id="reason-{{ $reason->Reason_id }}"
                                    action="{{ route('admin.reasons.update', $reason->Reason_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <label class="admin-sr-only"
                                        for="reason-name-{{ $reason->Reason_id }}">ชื่อประเภทการรายงาน</label>
                                    <input id="reason-name-{{ $reason->Reason_id }}" name="reason_name"
                                        value="{{ $reason->reason_name }}" maxlength="45" required>
                                </form>
                            </td>
                            <td>{{ $reason->reports_count }} รายการ</td>
                            <td>
                                <div class="admin-actions">
                                    <button class="admin-button secondary" type="submit"
                                        form="reason-{{ $reason->Reason_id }}">บันทึก</button>
                                    @if ($reason->reports_count === 0)
                                        <form action="{{ route('admin.reasons.destroy', $reason->Reason_id) }}"
                                            method="POST" onsubmit="return confirm('ยืนยันลบประเภทนี้ใช่ไหม?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-button danger" type="submit">ลบ</button>
                                        </form>
                                    @else<span class="admin-muted">มีรายงานใช้งานอยู่</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="admin-empty">ยังไม่มีประเภทการรายงาน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
