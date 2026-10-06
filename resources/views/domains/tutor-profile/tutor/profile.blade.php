@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'โปรไฟล์ติวเตอร์')

@section('content')

    <div class="profile-header mb-4">
        <h1>โปรไฟล์ติวเตอร์</h1>
        <p class="profile-description">
            จัดการและดูข้อมูลโปรไฟล์ติวเตอร์ของคุณ
        </p>
    </div>

    @if ($tutorProfile)
        <div class="card profile-card">
            <div class="card-body">
                <h2 class="section-title h4 mb-3">ข้อมูลโปรไฟล์</h2>
                <div class="profile-info-grid">
                    <div class="info-item mb-3">
                        <span class="info-label text-muted d-block">แนะนำตัว (Bio)</span>
                        <strong>
                            {{ $tutorProfile->bio ?: 'ยังไม่ได้เพิ่มข้อมูลแนะนำตัว' }}
                        </strong>
                    </div>

                    <div class="info-item mb-3">
                        <span class="info-label text-muted d-block">ประสบการณ์</span>

                        <strong>
                            {{ $tutorProfile->experience_years }} ปี
                        </strong>
                    </div>

                    <div class="info-item mb-3">
                        <span class="info-label text-muted d-block">คะแนนรีวิว</span>

                        <strong>
                            {{ number_format($tutorProfile->average_rating, 2) }} / 5.00
                        </strong>
                    </div>

                    <div class="info-item mb-3">
                        <span class="info-label text-muted d-block">รูปแบบการสอน</span>

                        <strong>
                            @if($tutorProfile->teaching_mode === 'online') ออนไลน์
                            @elseif($tutorProfile->teaching_mode === 'onsite') นัดเจอ (Onsite)
                            @else ทั้งสองแบบ
                            @endif
                        </strong>
                    </div>
                </div>

                <div class="profile-actions mt-4">
                    <a href="{{ route('tutor.profile.edit') }}" class="btn btn-primary">แก้ไขโปรไฟล์</a>
                </div>
            </div>
        </div>

    @else
        <div class="empty-profile text-center py-5">
            <h2>ไม่พบข้อมูลโปรไฟล์ติวเตอร์</h2>
            <p>
                คุณยังไม่มีข้อมูลโปรไฟล์สำหรับติวเตอร์
            </p>
        </div>

    @endif
@endsection