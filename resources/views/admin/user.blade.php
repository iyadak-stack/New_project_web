@extends('admin.layout')
@section('title', 'โปรไฟล์ผู้ใช้')
@section('description', 'ข้อมูลบัญชี โปรไฟล์นักเรียน และข้อมูลการสอนของผู้ใช้')
@section('content')
    @php
        $statusNames = ['pending' => 'รอตรวจสอบ', 'in_progress' => 'กำลังตรวจสอบ', 'resolved' => 'ปิดเรื่อง'];
        $tutorProfile = $member->tutorProfile;
        $openReports = $reportsAgainst->where('status', '!=', 'resolved')->count();
    @endphp

    {{-- member เป็นผู้ใช้ที่ Controller ค้นหาจาก user_id พร้อมข้อมูลโปรไฟล์ --}}
    <a class="admin-button secondary" href="{{ route('admin.users.index') }}">
        ← กลับรายชื่อผู้ใช้</a>
    <a class="admin-button secondary" href="{{ route('admin.reports.index') }}">
        ← กลับหน้ารายงาน</a>

    <section class="admin-panel admin-profile">
        <div class="admin-profile-avatar">
            @if ($member->profile_picture)
                <img src="{{ asset('storage/' . $member->profile_picture) }}" alt="รูปโปรไฟล์">
                @else{{ mb_substr($member->first_name, 0, 1) }}
            @endif
        </div>
        <div>
            <h2>{{ $member->first_name }} {{ $member->last_name }}</h2>
            <p>{{ $member->email }}</p>
            <p>รหัสผู้ใช้ {{ $member->user_id }}</p>
            <span
                class="admin-badge {{ $member->is_active ? 'green' : 'red' }}">{{ $member->is_active ? 'ใช้งานอยู่' : 'พักบัญชี' }}</span>
        </div>
    </section>

    <div class="admin-two-columns">
        <section class="admin-panel">
            <h2>ข้อมูลนักเรียน</h2>
            <p class="admin-description">{{ $member->studentProfile?->bio ?: 'ยังไม่ระบุข้อความแนะนำตัว' }}</p>
        </section>
        <section class="admin-panel">
            <h2>ข้อมูลการสอน</h2>
            @if ($tutorProfile)
                <p class="admin-description">{{ $tutorProfile->bio ?: 'ยังไม่ระบุข้อความแนะนำตัว' }}</p>
                <p>ประสบการณ์ {{ $tutorProfile->experience_years }} ปี</p>
                <p>วิชา: {{ $tutorProfile->subjects->pluck('subject_name')->implode(', ') ?: 'ยังไม่ระบุ' }}</p>
                <p>รูปแบบ:
                    {{ ['online' => 'ออนไลน์', 'onsite' => 'นัดเจอ', 'both' => 'ทั้งสองแบบ'][$tutorProfile->teaching_mode] ?? $tutorProfile->teaching_mode }}
                </p>
                <p>คะแนนเฉลี่ย: {{ number_format($tutorProfile->average_rating ?? 0, 2) }}</p>
                <p>สถานะใบสมัคร:
                    {{ ['approved' => 'อนุมัติแล้ว', 'pending' => 'รอตรวจสอบ', 'rejected' => 'ไม่ผ่าน', 'draft' => 'แบบร่าง'][$tutorProfile->approval_status] ?? ($tutorProfile->approval_status ?: 'ไม่ระบุ') }}
                </p>
            @else
                <p>ยังไม่มีโปรไฟล์ติวเตอร์</p>
            @endif
        </section>
    </div>

    <section class="admin-panel">
        <h2>ช่องทางติดต่อ</h2>
        <p>Line: {{ $member->contact?->line_id ?: 'ไม่ระบุ' }} · Discord: {{ $member->contact?->discord_id ?: 'ไม่ระบุ' }}
        </p>
    </section>

    @if ($tutorProfile)
        {{-- ประวัติรายงานที่ติวเตอร์คนนี้เคยโดน --}}
        <section class="admin-panel">
            <div class="admin-section-heading">
                <h2>รายงานที่ถูกแจ้ง <span>{{ $reportsAgainst->count() }} ครั้ง (ยังไม่ปิดเรื่อง {{ $openReports }})</span></h2>
            </div>
            @forelse ($reportsAgainst as $item)
                @php
                    $reporterName = trim(($item->reporter?->first_name ?? '') . ' ' . ($item->reporter?->last_name ?? ''));
                @endphp
                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                    <p style="margin: 0 0 4px;">
                        <strong>{{ $item->reason?->reason_name ?? 'ไม่พบประเภท' }}</strong>
                        · {{ $statusNames[$item->status] ?? $item->status }}
                    </p>
                    <p class="admin-muted" style="margin: 0 0 6px;">
                        รหัส {{ $item->Report_id }} · {{ $item->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}
                        · ผู้ส่ง: {{ $reporterName !== '' ? $reporterName : 'ไม่พบข้อมูลผู้ส่ง' }}
                    </p>
                    <div class="admin-description">{{ $item->description }}</div>
                </div>
            @empty
                <p class="admin-muted">ยังไม่เคยถูกรายงาน</p>
            @endforelse
        </section>

        {{-- รีวิวล่าสุด รวมที่ถูกซ่อน --}}
        <section class="admin-panel">
            <div class="admin-section-heading">
                <h2>รีวิวล่าสุด <span>{{ $reviews->count() }} รายการ</span></h2>
            </div>
            @forelse ($reviews as $review)
                @php
                    $appointment = $review->appointment;
                    $reviewer = $appointment?->studentProfile?->user ?? $appointment?->studentUser;
                    $reviewerName = trim(($reviewer?->first_name ?? '') . ' ' . ($reviewer?->last_name ?? ''));
                @endphp
                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                    <p style="margin: 0 0 4px;">
                        <strong>★ {{ number_format($review->rating ?? 0, 1) }}</strong>
                        · {{ $reviewerName !== '' ? $reviewerName : 'ไม่พบข้อมูลผู้รีวิว' }}
                        · {{ $review->created_at?->timezone('Asia/Bangkok')->format('d/m/Y') }}
                        @if ($review->trashed())
                            <span class="admin-badge red">ถูกซ่อน</span>
                        @endif
                    </p>
                    <div class="admin-description">{{ $review->Comment ?: 'ไม่มีข้อความรีวิว' }}</div>
                </div>
            @empty
                <p class="admin-muted">ยังไม่มีรีวิว</p>
            @endforelse
        </section>
    @endif
@endsection