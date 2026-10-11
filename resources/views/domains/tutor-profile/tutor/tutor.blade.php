<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลักติวเตอร์ - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-dashboard.css') }}?v={{ time() }}">
</head>
<body>

    <x-navbar />

    <main class="dashboard-container" style="max-width: 1200px; margin: 32px auto; padding: 0 20px;">

        <!-- ข้อความแจ้งเตือน Flash Message -->
        @if (session('success'))
            <div style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Banner ต้อนรับ -->
        <div style="background: #fdeee8; padding: 24px 32px; border-radius: 16px; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                ยินดีต้อนรับคุณ <span style="color: #e07a5f;">{{ Auth::user()->first_name }}</span> สู่ PeerTutor
            </h1>
            <p style="color: #64748b; font-size: 14px; margin: 0;">
                จัดการคำขอจอง ตารางสอน จากหน้านี้ พร้อมตัดสินใจตอบรับหรือปฏิเสธคำขอจองได้ทันที
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            
            <!-- ฝั่งซ้าย: สรุปคำขอจอง & ตารางคำขอ -->
            <div>
                <!-- กล่องสรุปจำนวน (ดึงจริงจาก DB) -->
                <div style="background: #ffffff; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">คำขอจอง</h2>
                        <span style="background: #fdeee8; color: #e07a5f; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                            {{ $pendingCount ?? 0 }} รายการ
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div style="background: #fff8f6; padding: 16px; border-radius: 12px; border: 1px solid #fdeee8;">
                            <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 4px;">รอตรวจสอบ</span>
                            <strong style="font-size: 24px; color: #e07a5f;">{{ $pendingCount ?? 0 }}</strong>
                        </div>
                        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 4px;">ตอบรับแล้ว</span>
                            <strong style="font-size: 24px; color: #1e293b;">{{ $confirmedCount ?? 0 }}</strong>
                        </div>
                        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 4px;">ปฏิเสธ</span>
                            <strong style="font-size: 24px; color: #64748b;">{{ $cancelledCount ?? 0 }}</strong>
                        </div>
                    </div>
                </div>

                <!-- ตารางคำขอจอง (ดึงข้อมูลจริง) -->
                <div style="background: #ffffff; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">ตารางคำขอจอง</h2>
                            <p style="font-size: 13px; color: #64748b; margin: 0;">ตรวจสอบรายละเอียดนักเรียน วันเวลา วิชา เพื่อตัดสินใจตอบรับหรือปฏิเสธ</p>
                        </div>
                        <a href="{{ route('appointments.index') }}" style="background: #fdeee8; color: #e07a5f; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none;">
                            ทั้งหมด {{ isset($pendingAppointments) ? $pendingAppointments->count() : 0 }} รายการ
                        </a>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; text-align: left; color: #64748b; font-size: 12px;">
                                <th style="padding: 10px;">นักเรียน</th>
                                <th style="padding: 10px;">วันเวลา</th>
                                <th style="padding: 10px;">วิชา</th>
                                <th style="padding: 10px;">สถานะ</th>
                                <th style="padding: 10px; text-align: center;">การตัดสินใจ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($pendingAppointments) && $pendingAppointments->count() > 0)
                                @foreach ($pendingAppointments as $appt)
                                    @php
                                        $student = $appt->studentProfile?->user;
                                        $dateStr = \Carbon\Carbon::parse($appt->start_datetime)->format('d/m/Y');
                                        $timeStr = \Carbon\Carbon::parse($appt->start_datetime)->format('H:i') . ' - ' . \Carbon\Carbon::parse($appt->end_datetime)->format('H:i');
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f8fafc;">
                                        <td style="padding: 12px 10px;">
                                            <strong style="color: #1e293b; display: block;">คุณ{{ $student?->first_name ?? 'นักเรียน' }}</strong>
                                            <span style="font-size: 12px; color: #94a3b8;">{{ $student?->email ?? '' }}</span>
                                        </td>
                                        <td style="padding: 12px 10px;">
                                            <div style="font-weight: 600; color: #334155;">{{ $dateStr }}</div>
                                            <span style="font-size: 12px; color: #64748b;">{{ $timeStr }}</span>
                                        </td>
                                        <td style="padding: 12px 10px;">
                                            <div style="font-weight: 600; color: #1e293b;">{{ $appt->subject->subject_name ?? '-' }}</div>
                                            <span style="font-size: 12px; color: #94a3b8;">{{ $appt->teaching_mode === 'onsite' ? 'Onsite' : 'ออนไลน์' }}</span>
                                        </td>
                                        <td style="padding: 12px 10px;">
                                            <span style="background: #fef3c7; color: #b45309; font-size: 12px; padding: 4px 10px; border-radius: 12px; font-weight: 600;">
                                                รอตรวจสอบ
                                            </span>
                                        </td>
                                        <td style="padding: 12px 10px; text-align: center;">
                                            <div style="display: flex; gap: 6px; justify-content: center;">
                                                <form action="{{ route('appointments.accept', $appt->Appointment_id ?? $appt->id) }}" method="POST" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" style="background: #e07a5f; color: #ffffff; border: none; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                        ตอบรับ
                                                    </button>
                                                </form>
                                                <form action="{{ route('appointments.reject', $appt->Appointment_id ?? $appt->id) }}" method="POST" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" style="background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                                        ปฏิเสธ
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 32px; color: #94a3b8;">
                                        🎉 ไม่มีคำขอจองที่รอการตรวจสอบในขณะนี้
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ฝั่งขวา: ตารางสอน/นัดหมายล่าสุด -->
            <div>
                <div style="background: #ffffff; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">ตารางสอนล่าสุด</h2>
                        <span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                            {{ isset($todaySchedules) ? $todaySchedules->count() : 0 }} คาบ
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @if (isset($todaySchedules) && $todaySchedules->count() > 0)
                            @foreach ($todaySchedules as $sched)
                                @php
                                    $student = $sched->studentProfile?->user;
                                    $timeStr = \Carbon\Carbon::parse($sched->start_datetime)->format('d/m/Y H:i');
                                    $isConfirmed = ($sched->status === 'confirmed');
                                @endphp

                                @if ($isConfirmed)
                                    <!-- กรณีสถานะ: ยืนยันแล้ว -->
                                    <div style="background: #f8fafc; padding: 12px 14px; border-radius: 12px; border-left: 4px solid #10b981;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                                            <strong style="font-size: 14px; color: #1e293b;">{{ $sched->subject->subject_name ?? '-' }}</strong>
                                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; background: #d1fae5; color: #047857;">
                                                ยืนยันแล้ว
                                            </span>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            น้อง{{ $student?->first_name ?? 'นักเรียน' }} • {{ $timeStr }} น.
                                        </div>
                                    </div>
                                @else
                                    <!-- กรณีสถานะ: รอการยืนยัน -->
                                    <div style="background: #f8fafc; padding: 12px 14px; border-radius: 12px; border-left: 4px solid #f59e0b;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                                            <strong style="font-size: 14px; color: #1e293b;">{{ $sched->subject->subject_name ?? '-' }}</strong>
                                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; background: #fef3c7; color: #b45309;">
                                                รอการยืนยัน
                                            </span>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            น้อง{{ $student?->first_name ?? 'นักเรียน' }} • {{ $timeStr }} น.
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p style="color: #94a3b8; font-size: 13px; text-align: center; margin: 20px 0;">ยังไม่มีตารางสอนเร็วๆ นี้</p>
                        @endif
                    </div>

                    <div style="margin-top: 16px; display: flex; gap: 8px;">
                        <a href="{{ route('appointments.index') }}" style="flex: 1; text-align: center; background: #e07a5f; color: #ffffff; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
                            ดูตารางทั้งหมด
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- 💡 JavaScript เปิด-ปิด Pop-over Card เมื่อคลิกรูปอวาตาร์โปรไฟล์ -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggleProfileBtn');
            const dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    } else {
                        dropdown.style.display = 'block';
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('active');
                        dropdown.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>