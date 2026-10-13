<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ติวเตอร์ - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
</head>
<body>

    <x-navbar />

    @php
        // เช็กรูปแบบการสอนของติวเตอร์ (ใช้ทั้งป้ายด้านบนและฟอร์มจอง)
        $mode = strtolower($tutorProfile->teaching_mode ?? '');
        $hasOnline = str_contains($mode, 'online');
        $hasOnsite = str_contains($mode, 'onsite');
        $isBoth = str_contains($mode, 'both') || ($hasOnline && $hasOnsite);

        // จำนวนรีวิว
        $reviewCount = isset($tutorProfile->reviews) ? count($tutorProfile->reviews) : 0;
    @endphp

    <main class="page" style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">

        <!-- ปุ่มย้อนกลับ -->
        <div style="margin-bottom: 20px;">
            {{-- เปิดหน้าหลักใหม่ เพื่อให้ใช้บทบาทล่าสุดหลังสลับ role --}}
            <button type="button" onclick="window.location.href='{{ route('home') }}'" style="background: #ffffff; border: 1px solid #e2e8f0; color: #64748b; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                ← ย้อนกลับ
            </button>
        </div>

        @if (session('success'))
            <div style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if ($errors->hasAny(['subject_id', 'tutor_id', 'appointment_date', 'appointment_time', 'start_datetime', 'end_datetime']))
            <div role="alert" style="padding: 14px; background: #fff2f2; color: #a53838; border-radius: 10px; margin-bottom: 16px;">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div style="display: grid; grid-template-columns: {{ Auth::check() ? '2fr 1fr' : '1fr' }}; gap: 24px;">

            <!-- ฝั่งซ้าย: ข้อมูลติวเตอร์ -->
            <div>

                <!-- การ์ดโปรไฟล์ -->
                <div style="background: #ffffff; padding: 28px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; gap: 24px; align-items: center; position: relative; margin-bottom: 24px;">

                    @if (!empty($tutorProfile->user->profile_picture))
                        <img src="{{ asset('storage/' . $tutorProfile->user->profile_picture) }}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;" alt="Tutor Avatar">
                    @else
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: #fdeee8; color: #e07a5f; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700;">
                            {{ mb_substr($tutorProfile->user->first_name ?? 'T', 0, 1) }}
                        </div>
                    @endif

                    <div style="flex: 1;">
                        <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                            <span style="background: #fdeee8; color: #e07a5f; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">✓ ยืนยันตัวตนแล้ว</span>
                            <span style="background: #f1f5f9; color: #475569; padding: 2px 10px; border-radius: 12px; font-size: 12px;">
                                @if ($isBoth)
                                    ออนไลน์ & Onsite
                                @elseif ($hasOnsite)
                                    ตัวต่อตัว (Onsite)
                                @else
                                    ออนไลน์ (Online)
                                @endif
                            </span>
                        </div>

                        <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">
                            พี่{{ $tutorProfile->user->first_name ?? 'ติวเตอร์' }} {{ $tutorProfile->user->last_name ?? '' }}
                        </h1>
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">
                            ประสบการณ์สอน {{ $tutorProfile->experience_years ?? 0 }} ปี
                        </p>
                        <div style="font-size: 13px; color: #e07a5f; font-weight: 600;">
                            ⭐ {{ number_format($tutorProfile->average_rating ?? 0, 1) }}
                            <span style="color: #94a3b8; font-weight: 400;">({{ $reviewCount }} รีวิว)</span>
                        </div>
                    </div>

                    @auth
                    <!-- ปุ่มหัวใจ กับปุ่มรายงาน -->
                    <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 8px; align-items: center;">
                        @if (!empty($isFavorite))
                            <form action="{{ route('tutor.favorite.destroy', $tutorProfile->tutor_id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="width: 40px; height: 40px; border-radius: 50%; background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48; font-size: 18px; cursor: pointer;" title="เอาออกจากรายการโปรด">♥</button>
                            </form>
                        @else
                            <form action="{{ route('tutor.favorite.store', $tutorProfile->tutor_id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 18px; cursor: pointer;" title="บันทึกเป็นรายการโปรด">♡</button>
                            </form>
                        @endif

                        <button type="button" onclick="openReportModal()" style="width: 40px; height: 40px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 15px; cursor: pointer;" title="รายงานติวเตอร์">
                            🚩
                        </button>
                    </div>
                    @endauth
                </div>

                <!-- รู้จักติวเตอร์ -->
                <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 12px 0;">🙋‍♂️ รู้จักติวเตอร์</h3>
                    <p style="font-size: 14px; color: #475569; line-height: 1.6; margin: 0;">
                        {{ $tutorProfile->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว' }}
                    </p>
                </div>

                <!-- วิชาที่สอน -->
                <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0;">📚 วิชาและระดับที่สอน</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        @if (!empty($tutorProfile->subjects) && count($tutorProfile->subjects) > 0)
                            @foreach ($tutorProfile->subjects as $subj)
                                <div style="background: #fff8f6; border: 1px solid #fdeee8; padding: 12px 16px; border-radius: 12px; min-width: 180px;">
                                    <strong style="color: #e07a5f; display: block; font-size: 14px;">{{ $subj->subject_name }}</strong>
                                    <span style="font-size: 12px; color: #64748b;">{{ $subj->description ?? 'เนื้อหาพื้นฐานและตะลุยโจทย์' }}</span>
                                </div>
                            @endforeach
                        @else
                            <p style="color: #94a3b8; font-size: 14px; margin: 0;">ยังไม่มีรายการวิชาที่เปิดสอน</p>
                        @endif
                    </div>
                </div>

                <!-- รีวิวจากผู้เรียน -->
                <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;">
                            💬 รีวิวจากผู้เรียนที่เคยเรียนจบแล้ว ({{ $reviewCount }})
                        </h3>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @if ($reviewCount > 0)
                            @foreach ($tutorProfile->reviews as $rev)
                                @php
                                    $reviewer = $rev->appointment?->studentProfile?->user;
                                @endphp
                                <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                                                {{ mb_substr($reviewer->first_name ?? 'S', 0, 1) }}
                                            </div>
                                            <div>
                                                <strong style="font-size: 14px; color: #1e293b; display: block;">คุณ{{ $reviewer->first_name ?? 'ผู้เรียน' }}</strong>
                                                <span style="font-size: 11px; color: #94a3b8;">{{ \Carbon\Carbon::parse($rev->created_at)->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                        <div style="color: #f59e0b; font-size: 13px; font-weight: 600;">
                                            ★ {{ number_format($rev->rating ?? 5, 1) }}
                                        </div>
                                    </div>
                                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">
                                        {{ $rev->Comment ?? 'ไม่มีข้อความรีวิว' }}
                                    </p>
                                </div>
                            @endforeach
                        @else
                            <div style="text-align: center; padding: 24px; color: #94a3b8; font-size: 14px;">
                                🍃 ยังไม่มีรีวิวสำหรับติวเตอร์ท่านนี้ (รีวิวได้หลังเรียนจบคาบเรียนแล้ว)
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @auth
            <!-- ฝั่งขวา: ฟอร์มจองเรียน -->
            <div>
                <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.02); position: sticky; top: 90px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 20px 0;">เริ่มเรียนกับพี่{{ $tutorProfile->user->first_name ?? '' }}</h3>

                    <form action="{{ route('appointments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tutor_id" value="{{ $tutorProfile->tutor_id }}">
                        {{-- ส่งชื่อช่องวันเวลาให้ตรงกับ Controller ของ branch เพื่อน --}}
                        <input type="hidden" name="start_datetime" id="booking_start">
                        <input type="hidden" name="end_datetime" id="booking_end">

                        <!-- เลือกวิชา -->
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">วิชาที่ต้องการเรียน</label>
                            <select name="subject_id" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; color: #1e293b;" required>
                                <option value="">-- เลือกวิชา --</option>
                                @if (!empty($tutorProfile->subjects))
                                    @foreach ($tutorProfile->subjects as $subj)
                                        <option value="{{ $subj->subject_id }}">{{ $subj->subject_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- รูปแบบการเรียน -->
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">รูปแบบการเรียน</label>
                            <p style="font-size: 13px; color: #475569; margin: 6px 0;">
    {{ ['online' => 'ออนไลน์', 'onsite' => 'นัดเจอ', 'both' => 'ออนไลน์และนัดเจอ'][$tutorProfile->teaching_mode] ?? 'ยังไม่ระบุ' }}
</p>
<p style="font-size: 12px; color: #64748b;">ใช้รูปแบบปัจจุบันของติวเตอร์ กรณีสอนทั้งสองแบบให้ตกลงกันก่อนเรียน</p>
                        </div>

                        <!-- เลือกวันที่ (ตารางว่างของติวเตอร์เก็บไว้ใน data-availabilities) -->
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">วันที่ติวเตอร์ว่างสอน</label>
                            <select name="appointment_date" id="appointment_date"
                                    data-availabilities="{{ json_encode($availabilities ?? []) }}"
                                    onchange="updateAvailableTimes()"
                                    style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; color: #1e293b;" required>
                                <option value="">-- เลือกวันที่ --</option>
                            </select>
                        </div>

                        <!-- เลือกเวลา -->
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">เวลาที่ติวเตอร์ว่างสอน</label>
                            <select name="appointment_time" id="appointment_time" onchange="updateBookingTimes()" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; color: #1e293b;" required disabled>
                                <option value="">-- โปรดเลือกวันที่ก่อน --</option>
                            </select>
                        </div>

                        <button type="submit" style="width: 100%; padding: 12px; background: #e07a5f; color: #ffffff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer;">
                            ส่งคำขอจองเรียน
                        </button>
                    </form>
                </div>
            </div>

            @endauth
        </div>

    </main>

    @auth
    <!-- Modal รายงานติวเตอร์ -->
    <div id="reportModal" role="dialog" aria-modal="true" aria-labelledby="reportTitle" style="display: {{ $errors->hasAny(['reason_type', 'description', 'reported_tutor_id']) ? 'flex' : 'none' }}; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto;">
        <div style="background: #ffffff; border-radius: 16px; padding: 24px; max-width: 480px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 id="reportTitle" style="margin: 0; font-size: 18px; color: #1e293b; font-weight: 700;">🚩 รายงานติวเตอร์คนนี้</h3>
                <button type="button" onclick="closeReportModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
            </div>

            {{-- onsubmit ทำงานหลังตรวจ required และถามยืนยันก่อนส่งข้อมูลจริง --}}
            <form action="{{ route('reports.store') }}" method="POST" onsubmit="return confirmReportSubmission(this)">
                @csrf
                <input type="hidden" name="reported_tutor_id" value="{{ $tutorProfile->tutor_id }}">

                <div style="margin-bottom: 12px;">
                    <label for="reportReason" style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">หัวข้อการรายงาน</label>
                    <select id="reportReason" name="reason_type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px;" required>
                        <option value="">-- เลือกหัวข้อที่ต้องการรายงาน --</option>
                        {{-- value เป็น Reason_id จริง จึงบันทึกเชื่อมกับตารางประเภทได้ --}}
                        @foreach ($reportReasons as $reason)
                            <option value="{{ $reason->Reason_id }}" @selected(old('reason_type') === $reason->Reason_id)>{{ $reason->reason_name }}</option>
                        @endforeach
                    </select>
                    @error('reason_type')
                        <p role="alert" style="color: #b91c1c; font-size: 13px;">{{ $message }}</p>
                    @enderror
                    @if ($reportReasons->isEmpty())
                        <p role="alert" style="color: #b91c1c; font-size: 13px;">ยังไม่มีประเภทการรายงาน กรุณาติดต่อผู้ดูแลระบบ</p>
                    @endif
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="reportDescription" style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">รายละเอียดเพิ่มเติม</label>
                    <textarea id="reportDescription" name="description" rows="4" maxlength="3000" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px;" placeholder="อธิบายรายละเอียดปัญหาที่พบ..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p role="alert" style="color: #b91c1c; font-size: 13px;">{{ $message }}</p>
                    @enderror
                    @error('reported_tutor_id')
                        <p role="alert" style="color: #b91c1c; font-size: 13px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeReportModal()" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #64748b; font-weight: 500; cursor: pointer;">ยกเลิก</button>
                    <button type="submit" @disabled($reportReasons->isEmpty()) style="padding: 10px 18px; border-radius: 8px; border: none; background: #ef4444; color: #ffffff; font-weight: 600; cursor: pointer;">ส่งรายงาน</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var dateSelect = document.getElementById('appointment_date');
        var timeSelect = document.getElementById('appointment_time');

        // ดึงตารางว่างของติวเตอร์จาก data-availabilities
        var availabilities = JSON.parse(dateSelect.dataset.availabilities || '[]');

        window.onload = function () {

            // ---------- เปิด-ปิด dropdown โปรไฟล์ของ navbar ----------
            var toggleBtn = document.getElementById('toggleProfileBtn');
            var dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');

                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    } else {
                        dropdown.style.display = 'block';
                    }
                });

                // คลิกที่อื่นให้ปิด
                document.addEventListener('click', function (e) {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('active');
                        dropdown.style.display = 'none';
                    }
                });
            }

            // ---------- ใส่วันที่ว่างลงใน select ----------
            if (availabilities.length === 0) {
                dateSelect.innerHTML = '<option value="">-- ติวเตอร์ยังไม่ได้ลงตารางว่าง --</option>';
                return;
            }

            var usedDates = [];

            for (var i = 0; i < availabilities.length; i++) {
                var d = availabilities[i].available_date;

                // ข้ามวันที่ซ้ำ
                if (d && usedDates.indexOf(d) === -1) {
                    usedDates.push(d);

                    var parts = d.split('-'); // ปี-เดือน-วัน
                    var option = document.createElement('option');
                    option.value = d;
                    option.textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
                    dateSelect.appendChild(option);
                }
            }
        };

        // พอเลือกวันที่ ให้โชว์เวลาว่างของวันนั้น
        // เลือกเวลาแล้วส่งวันเวลาเต็มให้ระบบจองตรวจซ้ำฝั่ง Server
        function updateBookingTimes() {
            var option = timeSelect.options[timeSelect.selectedIndex];
            document.getElementById('booking_start').value = option.dataset.start || '';
            document.getElementById('booking_end').value = option.dataset.end || '';
        }

        function updateAvailableTimes() {
            var selectedDate = dateSelect.value;
            document.getElementById('booking_start').value = '';
            document.getElementById('booking_end').value = '';

            if (selectedDate === '') {
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">-- โปรดเลือกวันที่ก่อน --</option>';
                return;
            }

            timeSelect.innerHTML = '<option value="">-- เลือกเวลา --</option>';
            var found = false;

            for (var i = 0; i < availabilities.length; i++) {
                var slot = availabilities[i];

                if (slot.available_date === selectedDate) {
                    found = true;

                    var start = slot.start_time ? slot.start_time.substring(0, 5) : '';
                    var end = slot.end_time ? slot.end_time.substring(0, 5) : '';
                    var text = start + ' - ' + end + ' น.';

                    var option = document.createElement('option');
                    option.value = text;
                    option.dataset.start = slot.start_datetime;
                    option.dataset.end = slot.end_datetime;
                    option.textContent = text;
                    timeSelect.appendChild(option);
                }
            }

            if (found) {
                timeSelect.disabled = false;
            } else {
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">-- ไม่มีช่วงเวลาว่างในวันนี้ --</option>';
            }
        }

        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }

        // กดยกเลิกใน popup จะไม่ส่งฟอร์ม และข้อมูลที่กรอกยังอยู่
        function confirmReportSubmission(form) {
            if (!window.confirm('คุณต้องการส่งรายงานติวเตอร์คนนี้ให้แอดมินตรวจสอบจริง ๆ ใช่ไหม?')) {
                return false;
            }

            // ปิดปุ่มหลังยืนยัน เพื่อป้องกันการกดซ้ำระหว่างรอ Server
            var submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'กำลังส่งรายงาน...';
            return true;
        }
    </script>
    @endauth
</body>
</html>
