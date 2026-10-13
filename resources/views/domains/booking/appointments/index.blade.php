<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางนัดหมาย - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <!-- ดึงไฟล์ CSS ตารางนัดหมายที่แยกไว้ -->
    <link rel="stylesheet" href="{{ asset('css/appointments.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Dynamic Navbar สลับตาม Role ใน Session -->
    <x-navbar />

    <main class="appt-page">

        <!-- ข้อความแจ้งเตือน Flash Message -->
        @if (session('success'))
            <div style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" style="background: #fff2f2; color: #a53838; padding: 14px; border-radius: 10px; margin-bottom: 16px;">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <!-- หัวข้อหน้า -->
        <div class="appt-header">
            <div class="appt-title">
                <h1>ตารางนัดหมาย</h1>
                @if (auth()->user()->current_role === 'tutor')
                    <p>ตรวจคำขอจากนักเรียน แล้วกดยืนยันหรือปฏิเสธการเรียน</p>
                @else
                    <p>ติดตามคำขอเรียนของคุณ และดูช่องทางเรียนหลังติวเตอร์ยืนยัน</p>
                @endif
            </div>
        </div>

        <!-- แท็บสลับสถานะ (Filters) -->
        <div class="appt-tabs">
            <a href="{{ route('appointments.index', ['status' => 'all']) }}" 
               class="tab-btn {{ ($status ?? 'all') === 'all' ? 'active' : '' }}">
                ทั้งหมด
            </a>
            <a href="{{ route('appointments.index', ['status' => 'pending']) }}" 
               class="tab-btn {{ ($status ?? '') === 'pending' ? 'active' : '' }}">
                ⏳ รอการยืนยัน
            </a>
            <a href="{{ route('appointments.index', ['status' => 'confirmed']) }}" 
               class="tab-btn {{ ($status ?? '') === 'confirmed' ? 'active' : '' }}">
                ✅ ยืนยันแล้ว
            </a>
            <a href="{{ route('appointments.index', ['status' => 'cancelled']) }}" 
               class="tab-btn {{ ($status ?? '') === 'cancelled' ? 'active' : '' }}">
                ❌ ยกเลิกแล้ว
            </a>
        </div>

        <!-- การ์ดรายการนัดหมาย -->
        <div class="appt-grid">
            @if (isset($appointments) && $appointments->count() > 0)
                @foreach ($appointments as $appt)
                    @php
                        $startDate = \Carbon\Carbon::parse($appt->start_datetime ?? $appt->appointment_date)->format('d/m/Y');
                        $startTime = \Carbon\Carbon::parse($appt->start_datetime ?? $appt->appointment_time)->format('H:i');
                        $endTime = $appt->end_datetime ? \Carbon\Carbon::parse($appt->end_datetime)->format('H:i น.') : '';
                        
                        $isTutor = auth()->user()->current_role === 'tutor';
                        $otherUser = $isTutor 
                            ? ($appt->studentProfile?->user ?? $appt->student)
                            : ($appt->tutorProfile?->user ?? $appt->tutor);
                        $roleLabel = $isTutor ? '🎓 นักเรียน' : '👨‍🏫 ติวเตอร์';
                    @endphp

                    <div class="appt-card">
                        <div>
                            <!-- หัวการ์ด: ชื่อวิชา & Badge สถานะ -->
                            <div class="appt-card-head">
                                <div class="subject-badge">
                                    📘 {{ $appt->subject->subject_name ?? 'วิชาทั่วไป' }}
                                </div>
                                <div>
                                    @if ($appt->status === 'confirmed')
                                        <span class="status-pill status-confirmed">✓ ยืนยันแล้ว</span>
                                    @elseif ($appt->status === 'cancelled')
                                        <span class="status-pill status-cancelled">✕ ยกเลิก</span>
                                    @else
                                        <span class="status-pill status-pending">⏳ รอตรวจสอบ</span>
                                    @endif
                                </div>
                            </div>

                            <!-- ข้อมูลคู่เรียน/ติวเตอร์ -->
                            <div class="student-info">
                                @if ($otherUser?->profile_picture)
                                    <img src="{{ asset('storage/' . $otherUser->profile_picture) }}" class="student-avatar" alt="Avatar">
                                @else
                                    <div class="student-placeholder">
                                        {{ mb_substr($otherUser?->first_name ?? 'U', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="student-name">คุณ{{ $otherUser?->first_name ?? 'ผู้ใช้' }} {{ $otherUser?->last_name ?? '' }}</h4>
                                    <p class="student-role">{{ $roleLabel }}</p>
                                </div>
                            </div>

                            <!-- วัน เวลา และรูปแบบการเรียน -->
                            <div class="appt-details">
                                <div class="detail-item">
                                    📆 <strong>วันที่:</strong> {{ $startDate }}
                                </div>
                                <div class="detail-item">
                                    ⏰ <strong>เวลา:</strong> {{ $startTime }} {{ $endTime ? '- ' . $endTime : '' }}
                                </div>
                                <div class="detail-item">
                                    💻 <strong>รูปแบบ:</strong> 
                                    {{ ['online'=>'ออนไลน์', 'onsite'=>'นัดเจอ', 'both'=>'ออนไลน์และนัดเจอ'][$appt->tutorProfile?->teaching_mode] ?? 'ยังไม่ระบุ' }}
                                </div>
                                @if (!empty($appt->note))
                                    <div style="background: #f8fafc; padding: 8px 10px; border-radius: 8px; font-size: 12px; color: #64748b; margin-top: 4px;">
                                        💬 <i>"{{ $appt->note }}"</i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- ปุ่มจัดการคำขอ / ดูรายละเอียด -->
                        <div class="appt-actions">
                            @if ($appt->status === 'pending' && $isTutor)
                                <form action="{{ route('appointments.accept', $appt->Appointment_id ?? $appt->id) }}" method="POST" style="margin:0; flex:1;">
                                    @csrf
                                    <button type="submit" class="btn-accept" style="width:100%;" onclick="return confirm('ยืนยันรับคำขอเรียนนี้ใช่ไหม?')">ยืนยันคำขอ</button>
                                </form>

                                <form action="{{ route('appointments.reject', $appt->Appointment_id ?? $appt->id) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-reject" onclick="return confirm('ยืนยันปฏิเสธคำขอนี้?')">ปฏิเสธ</button>
                                </form>
                            @else
                                <a href="{{ route('appointments.show', $appt->Appointment_id ?? $appt->id) }}" class="btn-outline">
                                    🔎 ดูรายละเอียดนัดหมาย
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <!-- กรณีไม่มีข้อมูล -->
                <div class="empty-box">
                    <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
                    <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">ยังไม่มีรายการนัดหมายในหมวดนี้</h2>
                    <p style="font-size: 14px; color: #64748b; margin: 0;">เมื่อมีรายการนัดหมายเข้ามาจะแสดงผลในหน้านี้ทันที</p>
                </div>
            @endif
        </div>

    </main>

    <!-- JS สำหรับ Pop-over Card Profile บน Navbar -->
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
