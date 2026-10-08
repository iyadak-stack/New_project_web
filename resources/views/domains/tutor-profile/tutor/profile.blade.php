@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'โปรไฟล์ติวเตอร์')

@section('content')

    <div class="profile-header mb-4">
        <h1>โปรไฟล์ติวเตอร์</h1>
        <p class="profile-description">
            จัดการและดูข้อมูลโปรไฟล์ติวเตอร์ของคุณ
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card profile-card">
        <div class="card-body">
            <h2 class="section-title h4 mb-3">ข้อมูลโปรไฟล์</h2>

            <div class="mb-3">
                <span class="info-label text-muted d-block">ชื่อ-นามสกุล</span>
                <strong>{{ auth()->user()->name }}</strong>
            </div>

            @if ($tutorProfile)

                <div class="mb-3">
                    <span class="info-label text-muted d-block">แนะนำตัว (Bio)</span>
                    <strong>{{ $tutorProfile->bio ?: 'ยังไม่ได้เพิ่มข้อมูลแนะนำตัว' }}</strong>
                </div>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">ประสบการณ์</span>
                    <strong>{{ $tutorProfile->experience_years ?? 0 }} ปี</strong>
                </div>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">คะแนนรีวิว</span>
                    <strong>{{ number_format($tutorProfile->average_rating, 2) }} / 5.00</strong>
                </div>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">รูปแบบการสอน</span>
                    <strong>
                        @if ($tutorProfile->teaching_mode === 'online') ออนไลน์
                        @elseif ($tutorProfile->teaching_mode === 'onsite') นัดเจอ (Onsite)
                        @else ทั้งสองแบบ
                        @endif
                    </strong>
                </div>

                <h2 class="section-title h4 mb-3 mt-4">ช่องทางการติดต่อ</h2>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">LINE ID</span>
                    <strong>{{ $tutorProfile->line_id ?: 'ยังไม่ได้ระบุ' }}</strong>
                </div>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">Discord</span>
                    <strong>{{ $tutorProfile->discord_id ?: 'ยังไม่ได้ระบุ' }}</strong>
                </div>

                <div class="mb-3">
                    <span class="info-label text-muted d-block">Zoom Meeting Link</span>
                    @if ($tutorProfile->zoom_link)
                        <a href="{{ $tutorProfile->zoom_link }}" target="_blank" rel="noopener">
                            <strong>{{ $tutorProfile->zoom_link }}</strong>
                        </a>
                    @else
                        <strong>ยังไม่ได้ระบุ</strong>
                    @endif
                </div>

            @else
                <div class="mb-3">
                    <span class="info-label text-muted d-block">ข้อมูลติวเตอร์</span>
                    <p class="text-muted mb-0">คุณยังไม่มีข้อมูลโปรไฟล์ติวเตอร์</p>
                </div>
            @endif

            <div class="profile-actions mt-4">
                <a href="{{ route('tutor.profile.edit') }}" class="btn btn-primary">
                    แก้ไขโปรไฟล์
                </a>
            </div>
        </div>
    </div>

@endsection