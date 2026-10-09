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
        <div class="alert alert-success mb-4" style="background-color: #d1e7dd; color: #0f5132; padding: 12px 16px; border-radius: 8px; border: 1px solid #badbcc;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card profile-card" style="background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div class="card-body">
            
            <!-- ส่วนการ์ดโปรไฟล์ส่วนตัว -->
            <div style="display: flex; align-items: center; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid #f3f4f6; margin-bottom: 24px;">
                <img src="{{ auth()->user()->profile_photo_url ?? auth()->user()->avatar ?? asset('images/default-avatar.png') }}" 
                     alt="Profile Picture" 
                     style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e07a5f;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0;">
                        คุณ {{ auth()->user()->first_name ?? auth()->user()->name }} {{ auth()->user()->last_name }}
                    </h2>
                    <span style="display: inline-block; background: #fff3eb; color: #e07a5f; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                        บทบาท: ติวเตอร์
                    </span>
                </div>
            </div>

            @if ($tutorProfile)
                <!-- ข้อมูลการสอน -->
                <h3 style="font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 16px;">📌 ข้อมูลการสอน</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div style="background: #f9fafb; padding: 12px 16px; border-radius: 8px;">
                        <span class="text-muted" style="font-size: 13px; color: #6b7280; display: block;">ประสบการณ์</span>
                        <strong style="font-size: 15px; color: #111827;">{{ $tutorProfile->experience_years ?? 0 }} ปี</strong>
                    </div>

                    <div style="background: #f9fafb; padding: 12px 16px; border-radius: 8px;">
                        <span class="text-muted" style="font-size: 13px; color: #6b7280; display: block;">คะแนนรีวิว</span>
                        <strong style="font-size: 15px; color: #111827;">⭐ {{ number_format($tutorProfile->average_rating, 2) }} / 5.00</strong>
                    </div>

                    <div style="background: #f9fafb; padding: 12px 16px; border-radius: 8px;">
                        <span class="text-muted" style="font-size: 13px; color: #6b7280; display: block;">รูปแบบการสอน</span>
                        <strong style="font-size: 15px; color: #111827;">
                            @if ($tutorProfile->teaching_mode === 'online') ออนไลน์
                            @elseif ($tutorProfile->teaching_mode === 'onsite') นัดเจอ (Onsite)
                            @else ทั้งสองแบบ
                            @endif
                        </strong>
                    </div>
                </div>

                <div style="margin-bottom: 24px; background: #f9fafb; padding: 16px; border-radius: 8px;">
                    <span class="text-muted" style="font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">แนะนำตัว (Bio)</span>
                    <p style="font-size: 15px; color: #374151; margin: 0; line-height: 1.5;">
                        {{ $tutorProfile->bio ?: 'ยังไม่ได้เพิ่มข้อมูลแนะนำตัว' }}
                    </p>
                </div>

                <!-- ช่องทางติดต่อในการสอน -->
                <div class="contact-channels-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        📞 ช่องทางติดต่อในการสอน
                    </h3>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: #334155;">
                            <strong style="width: 130px; color: #64748b;">• LINE ID:</strong>
                            <span>{{ $tutorProfile->line_id ?: 'ยังไม่ได้ระบุ' }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: #334155;">
                            <strong style="width: 130px; color: #64748b;">• Discord:</strong>
                            <span>{{ $tutorProfile->discord_id ?: 'ยังไม่ได้ระบุ' }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: #334155;">
                            <strong style="width: 130px; color: #64748b;">• Zoom Link:</strong>
                            @if ($tutorProfile->zoom_link)
                                <a href="{{ $tutorProfile->zoom_link }}" target="_blank" rel="noopener" style="color: #2563eb; text-decoration: underline; word-break: break-all;">
                                    {{ $tutorProfile->zoom_link }}
                                </a>
                            @else
                                <span>ยังไม่ได้ระบุ</span>
                            @endif
                        </li>
                    </ul>
                </div>

            @else
                <div style="background: #f9fafb; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 24px;">
                    <p class="text-muted" style="margin: 0; color: #6b7280;">คุณยังไม่มีข้อมูลโปรไฟล์ติวเตอร์</p>
                </div>
            @endif

            <div class="profile-actions" style="display: flex; gap: 12px; margin-top: 16px;">
                <a href="{{ route('tutor.profile.edit') }}" class="btn btn-primary" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
                    แก้ไขโปรไฟล์
                </a>
            </div>
        </div>
    </div>

@endsection