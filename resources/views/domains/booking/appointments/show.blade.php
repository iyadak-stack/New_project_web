<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการนัดหมาย - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/appointments.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Dynamic Navbar -->
    <x-navbar />

    <main class="appt-page" style="max-width: 720px;">

        <!-- ปุ่มย้อนกลับ -->
        <div style="margin-bottom: 16px;">
            <a href="{{ route('appointments.index') }}" style="color: #64748b; text-decoration: none; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                ← ย้อนกลับไปตารางนัดหมาย
            </a>
        </div>

        <div style="background: #ffffff; border-radius: 20px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            
            <!-- Header ส่วนหัว -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span style="font-size: 12px; color: #94a3b8; font-weight: 600; display: block; margin-bottom: 4px;">
                        รหัสการนัดหมาย #{{ $appointment->Appointment_id ?? $appointment->id }}
                    </span>
                    <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0;">
                        📘 {{ $appointment->subject->subject_name ?? 'วิชาทั่วไป' }}
                    </h1>
                </div>

                <div>
                    @if ($appointment->status === 'confirmed')
                        <span class="status-pill status-confirmed" style="font-size: 13px; padding: 6px 14px;">✓ ยืนยันแล้ว</span>
                    @elseif ($appointment->status === 'cancelled')
                        <span class="status-pill status-cancelled" style="font-size: 13px; padding: 6px 14px;">✕ ยกเลิกแล้ว</span>
                    @else
                        <span class="status-pill status-pending" style="font-size: 13px; padding: 6px 14px;">⏳ รอตรวจสอบ</span>
                    @endif
                </div>
            </div>

            <!-- ข้อมูลผู้เข้าร่วม (ติวเตอร์ & นักเรียน) -->
            @php
                $tutorUser = $appointment->tutorProfile?->user ?? $appointment->tutor;
                $studentUser = $appointment->studentProfile?->user ?? $appointment->student;
            @endphp

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <!-- ฝั่งติวเตอร์ -->
                <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid #e07a5f;">
                        @if ($tutorUser?->profile_picture)
                            <img src="{{ asset('storage/' . $tutorUser->profile_picture) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <div style="width:100%; height:100%; background:#fdeee8; color:#e07a5f; font-weight:700; display:flex; align-items:center; justify-content:center;">
                                {{ mb_substr($tutorUser?->first_name ?? 'T', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #94a3b8; font-weight: 600; display: block;">👨‍🏫 ติวเตอร์</span>
                        <strong style="font-size: 14px; color: #1e293b;">คุณ{{ $tutorUser?->first_name ?? '-' }} {{ $tutorUser?->last_name ?? '' }}</strong>
                    </div>
                </div>

                <!-- ฝั่งนักเรียน -->
                <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid #3b82f6;">
                        @if ($studentUser?->profile_picture)
                            <img src="{{ asset('storage/' . $studentUser->profile_picture) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <div style="width:100%; height:100%; background:#eff6ff; color:#3b82f6; font-weight:700; display:flex; align-items:center; justify-content:center;">
                                {{ mb_substr($studentUser?->first_name ?? 'S', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #94a3b8; font-weight: 600; display: block;">🎓 นักเรียน</span>
                        <strong style="font-size: 14px; color: #1e293b;">คุณ{{ $studentUser?->first_name ?? '-' }} {{ $studentUser?->last_name ?? '' }}</strong>
                    </div>
                </div>
            </div>

            <!-- รายละเอียดวัน เวลา รูปแบบ -->
            <div style="background: #ffffff; border: 1px solid #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 24px;">
                <h3 style="font-size: 15px; font-weight: 600; color: #475569; margin: 0 0 16px 0;">⏰ รายละเอียดเวลานัดหมาย</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 14px; color: #334155;">
                    <div>
                        <span style="color: #94a3b8; font-size: 12px; display: block;">วัน-เวลานัดเรียน</span>
                        <strong>{{ \Carbon\Carbon::parse($appointment->start_datetime)->format('d/m/Y H:i') }} น.</strong>
                    </div>
                    <div>
                        <span style="color: #94a3b8; font-size: 12px; display: block;">เวลาสิ้นสุด</span>
                        <strong>{{ $appointment->end_datetime ? \Carbon\Carbon::parse($appointment->end_datetime)->format('H:i น.') : '-' }}</strong>
                    </div>
                    <div>
                        <span style="color: #94a3b8; font-size: 12px; display: block;">รูปแบบการสอน</span>
                        <strong>{{ ['online'=>'ออนไลน์', 'onsite'=>'นัดเจอ', 'both'=>'ออนไลน์และนัดเจอ'][$appointment->tutorProfile?->teaching_mode] ?? 'ยังไม่ระบุ' }}</strong>
                    </div>
                </div>
            </div>

            {{-- ตามที่ตกลง ใช้ช่องทางปัจจุบันในโปรไฟล์ ไม่สร้างคอลัมน์ของนัดหมายเพิ่ม --}}
            @if ($appointment->status === 'confirmed')
                @php $contact = $appointment->tutorProfile?->user?->contact; @endphp
                <section style="background: #fdeee8; border: 1px solid #f6cfc2; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <h2 style="font-size: 16px; margin: 0 0 12px;">ช่องทางเรียนและติดต่อ</h2>
                    <p style="font-size: 13px;">Line: {{ $contact?->line_id ?: 'ยังไม่ระบุ' }} · Discord: {{ $contact?->discord_id ?: 'ยังไม่ระบุ' }}</p>
                    @if ($contact?->zoom_link)
                        <p><a href="{{ $contact->zoom_link }}" target="_blank" rel="noopener noreferrer">เปิดห้องเรียน Zoom</a></p>
                    @endif
                    @if ($contact?->google_meet_link)
                        <p><a href="{{ $contact->google_meet_link }}" target="_blank" rel="noopener noreferrer">เปิดห้องเรียน Google Meet</a></p>
                    @endif
                    <p style="font-size: 12px; color: #64748b;">ข้อมูลนี้มาจากโปรไฟล์ปัจจุบันของติวเตอร์ หากนัดเจอให้ติดต่อเพื่อตกลงสถานที่</p>
                </section>
            @elseif ($appointment->status === 'pending')
                <p style="font-size: 13px; color: #64748b;">รอติวเตอร์ยืนยันคำขอ ก่อนเปิดดูช่องทางเรียน</p>
            @endif

            <!-- หมายเหตุเพิ่มเติม -->
            @if (!empty($appointment->note))
                <div style="margin-bottom: 24px; background: #fffbe0; padding: 16px; border-radius: 12px; border: 1px solid #fef08a;">
                    <strong style="font-size: 13px; color: #854d0e; display: block; margin-bottom: 4px;">💬 สิ่งที่ต้องการเน้นเพิ่มเติม:</strong>
                    <p style="margin: 0; font-size: 14px; color: #713f12; line-height: 1.5;">{{ $appointment->note }}</p>
                </div>
            @endif

            <!-- ปุ่มดำเนินงานด้านล่าง -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('appointments.index') }}" class="btn-outline" style="max-width: 160px; text-align: center;">
                    ย้อนกลับ
                </a>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggleProfileBtn');
            const dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
