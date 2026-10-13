@extends('admin.layout')
@section('title', 'ภาพรวมระบบ')
@section('description', 'ดูจำนวนผู้ใช้ การนัดหมาย และรายงานที่ต้องตรวจสอบในที่เดียว')
@section('content')
    @php
        // กำหนดชื่อการ์ดและตัวกรองไว้ชัดเจน เพื่อเปลี่ยนข้อความได้ง่าย
        $statCards = [
            ['key' => 'users', 'label' => 'ผู้ใช้ทั้งหมด', 'route' => 'admin.users.index', 'filters' => []],
            [
                'key' => 'students',
                'label' => 'นักเรียน',
                'route' => 'admin.users.index',
                'filters' => ['role' => 'student'],
            ],
            [
                'key' => 'tutors',
                'label' => 'ติวเตอร์',
                'route' => 'admin.users.index',
                'filters' => ['role' => 'tutor'],
            ],
            ['key' => 'appointments', 'label' => 'นัดหมาย', 'route' => 'admin.appointments.index', 'filters' => []],
            [
                'key' => 'pendingReports',
                'label' => 'รายงานรอตรวจสอบ',
                'route' => 'admin.reports.index',
                'filters' => ['status' => 'pending'],
            ],
            [
                'key' => 'suspended',
                'label' => 'บัญชีที่ถูกพัก',
                'route' => 'admin.users.index',
                'filters' => ['active' => '0'],
            ],
        ];
    @endphp
    <div class="admin-stats">
        @foreach ($statCards as $card)
            <a class="admin-stat" href="{{ route($card['route'], $card['filters']) }}">
                <span>{{ $card['label'] }}</span>
                <strong>{{ number_format($stats[$card['key']]) }}</strong>
                <small>ดูรายการ →</small>
            </a>
        @endforeach
    </div>
    <section class="admin-panel">
        <div class="admin-section-heading">
            <div>
                <h2>รายงานล่าสุด</h2>
                <p>เรียงจากวันที่ผู้ใช้ส่งรายงาน</p>
            </div>
            <a class="admin-button secondary" href="{{ route('admin.reports.index') }}">ดูรายงานทั้งหมด →</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ประเภท / ผู้ส่ง</th>
                        <th>ติวเตอร์</th>
                        <th>สถานะ</th>
                        <th>วันที่ส่ง</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse ($latestReports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->reason?->reason_name }}</strong>
                                <small>{{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }}</small>
                            </td>
                            <td>{{ $report->tutorProfile?->user?->first_name ?? 'ไม่พบติวเตอร์' }}</td>
                            <td>
                                @include('admin.partials.status', ['status' => $report->status])</td>
                            <td>{{ $report->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-empty">ยังไม่มีรายงานจากผู้ใช้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
