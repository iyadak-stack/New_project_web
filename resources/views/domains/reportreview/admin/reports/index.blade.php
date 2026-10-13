@extends('admin.layout')
@section('title', 'จัดการรายงาน')
@section('description', 'ตรวจปัญหาที่ผู้ใช้แจ้ง และบันทึกสถานะล่าสุดของแต่ละรายงาน')
@section('content')
<div class="admin-subnav">
<a class="is-active" href="{{ route('admin.reports.index') }}">รายงานจากผู้ใช้</a>
<a href="{{ route('admin.reasons.index') }}">ประเภทการรายงาน</a>
</div>
<form class="admin-filters" method="GET">
    <label>ค้นหารายงาน<input name="search" value="{{ request('search') }}" placeholder="รหัสรายงาน ชื่อ หรือรายละเอียด">
</label>
    <label>สถานะ<select name="status">
<option value="">ทั้งหมด</option>
@foreach (['pending'=>'รอตรวจสอบ','in_progress'=>'กำลังตรวจสอบ','resolved'=>'ปิดเรื่อง'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>
@endforeach</select>
</label>
    <label>ประเภท<select name="reason">
<option value="">ทั้งหมด</option>
@foreach ($reasons as $reason)<option value="{{ $reason->Reason_id }}" @selected(request('reason')===$reason->Reason_id)>{{ $reason->reason_name }}</option>
@endforeach</select>
</label>
    <button class="admin-button" type="submit">ค้นหา</button>
<a class="admin-reset" href="{{ route('admin.reports.index') }}">ล้างตัวกรอง</a>
</form>
<div class="admin-section-heading">
<h2>รายงานทั้งหมด <span>{{ $reports->total() }} รายการ</span>
</h2>
</div>
@forelse ($reports as $report)
<article class="admin-panel">
    <div class="admin-section-heading">
<h2>{{ $report->reason?->reason_name ?? 'ไม่พบประเภท' }}</h2>
@include('admin.partials.status',['status'=>$report->status])</div>
    <p class="admin-muted">รหัส {{ $report->Report_id }} · ส่งเมื่อ {{ $report->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</p>
    <div class="admin-two-columns">
<p>
<strong>ผู้ส่ง:</strong> {{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }} ({{ $report->Users_user_id }})</p>
<p>
<strong>ติวเตอร์ที่ถูกรายงาน:</strong> {{ $report->tutorProfile?->user?->first_name }} {{ $report->tutorProfile?->user?->last_name }} ({{ $report->Tutor_profiles_tutor_id ?: 'ยังไม่ระบุ' }})</p>
</div>
    {{-- แสดงเป็นข้อความธรรมดา ป้องกัน HTML/Script ที่ผู้ใช้กรอก --}}
    <div class="admin-description">{{ $report->description }}</div>
    <div class="admin-report-footer">
<span class="admin-muted">แก้ไขล่าสุด {{ $report->updated_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</span>
<form class="admin-actions" method="POST" action="{{ route('admin.reports.status',$report->Report_id) }}">
@csrf
@method('PATCH')
<label class="admin-sr-only" for="status-{{ $report->Report_id }}">สถานะรายงาน</label>
<select id="status-{{ $report->Report_id }}" name="status">
@foreach (['pending'=>'รอตรวจสอบ','in_progress'=>'กำลังตรวจสอบ','resolved'=>'ปิดเรื่อง'] as $value=>$label)<option value="{{ $value }}" @selected($report->status===$value)>{{ $label }}</option>
@endforeach</select>
<button class="admin-button" type="submit">บันทึกสถานะ</button>
</form>
</div>
</article>
@empty
    <div class="admin-panel admin-empty">ไม่พบรายงานตามเงื่อนไข</div>
@endforelse
@include('admin.partials.pagination',['items'=>$reports])
@endsection
