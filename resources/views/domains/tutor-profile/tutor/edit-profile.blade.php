<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($isApplication ?? false) ? 'คำขอเป็นติวเตอร์' : 'แก้ไขโปรไฟล์ติวเตอร์' }} - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-profile-edit.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

@php
    $isApplication = $isApplication ?? false;
    $isPending = $isApplication && $tutorProfile?->approval_status === 'pending';
    $user = auth()->user();
    $avatarUrl = $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : ($user->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&color=e07a5f&background=fdeee8');

    // ดึง Contact ของติวเตอร์ (ตาราง Contact ใน ER)
    $contact = $user->contact;

    // ID ของวิชาที่ติวเตอร์เลือกไว้แล้ว (ตาราง Tutor_profiles_has_Subject)
    $selectedSubjectIds = [];
    $previousSubjects = old('subjects', $tutorProfile?->subjects->pluck('subject_id')->toArray() ?? []);
    // คืนวิชาที่เคยเลือกไว้ เมื่อส่งฟอร์มแล้ว validation ไม่ผ่าน
    foreach ((array) $previousSubjects as $subjectId) {
        $selectedSubjectIds[] = (string) $subjectId;
    }

@endphp

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="pe-page">
        <div class="pe-card">

            <h1 class="pe-title">{{ $isApplication ? 'คำขอเป็นติวเตอร์' : 'แก้ไขโปรไฟล์ติวเตอร์' }}</h1>
            <p class="pe-sub">{{ $isApplication ? 'กรอกข้อมูลการสอนและส่งให้แอดมินตรวจสอบ ก่อนใช้งานบทบาทติวเตอร์' : 'อัปเดตข้อมูลส่วนตัว ข้อมูลการสอน และช่องทางการติดต่อของคุณ' }}</p>
            @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p role="alert">{{ session('error') }}</p>@endif
            @if ($isPending)
                <p class="pe-sub">สถานะ: รอแอดมินอนุมัติ ระหว่างนี้ใช้งานเป็นนักเรียนได้</p>
            @elseif ($isApplication && $tutorProfile?->approval_status === 'rejected')
                <p role="alert">คำขอไม่ผ่าน: {{ $tutorProfile->reject_reason }} กรุณาแก้ไขข้อมูลและส่งใหม่</p>
            @endif

            @if ($errors->any())
                <div class="pe-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route($isApplication ? 'tutor.application.submit' : 'tutor.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ระหว่างรอตรวจ ไม่ให้เปลี่ยนข้อมูลที่แอดมินกำลังตรวจ --}}
                <fieldset @disabled($isPending) style="border: none; padding: 0; margin: 0; min-width: 0;">
                <!-- รูปโปรไฟล์ -->
                <div class="pe-avatar-wrap">
                    <div class="pe-avatar">
                        <img id="avatar-preview" src="{{ $avatarUrl }}" alt="Profile Image">
                    </div>
                    <label for="avatar" class="pe-avatar-btn">เปลี่ยนรูปโปรไฟล์</label>
                    <input type="file" id="avatar" name="avatar" style="display: none;" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- ข้อมูลส่วนตัว (Users Table) -->
                <div class="pe-section">
                    <h2>ข้อมูลส่วนตัว</h2>
                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="first_name">ชื่อจริง</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>
                        <div class="pe-field">
                            <label for="last_name">นามสกุล</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลการสอน (Tutor_profiles Table) -->
                <div class="pe-section">
                    <h2>ข้อมูลการสอน</h2>

                    <div class="pe-field" style="margin-bottom: 16px;">
                        <label for="bio">แนะนำตัว (Bio)</label>
                        <textarea id="bio" name="bio" rows="3"
                                  placeholder="แนะนำตัว สไตล์การสอน หรือสิ่งที่นักเรียนจะได้รับ">{{ old('bio', $tutorProfile?->bio) }}</textarea>
                    </div>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="experience_years">ประสบการณ์ (ปี)</label>
                            <input type="number" id="experience_years" name="experience_years" min="0"
                                   value="{{ old('experience_years', $tutorProfile?->experience_years ?? 0) }}">
                        </div>
                        <div class="pe-field">
                            <label for="teaching_mode">รูปแบบการสอน</label>
                            <select id="teaching_mode" name="teaching_mode" required>
                                <option value="">เลือกรูปแบบการสอน</option>
                                <option value="online" @selected(old('teaching_mode', $tutorProfile?->teaching_mode) === 'online')>ออนไลน์</option>
                                <option value="onsite" @selected(old('teaching_mode', $tutorProfile?->teaching_mode) === 'onsite')>นัดเจอ</option>
                                <option value="both" @selected(old('teaching_mode', $tutorProfile?->teaching_mode) === 'both')>ออนไลน์และนัดเจอ</option>
                            </select>
                        </div>
                    </div>

                    {{-- ใช้ checkbox ส่งวิชาเข้า Controller โดยตรง ไม่ต้องสร้าง hidden input ด้วย JavaScript --}}
                    <div class="pe-field" style="margin-top: 16px;">
                        <p>วิชาที่สอน (เลือกอย่างน้อย 1 วิชา)</p>
                        @forelse ($allSubjects as $subject)
                            <label for="subject-{{ $subject->subject_id }}">
                                <input type="checkbox" id="subject-{{ $subject->subject_id }}"
                                       name="subjects[]" value="{{ $subject->subject_id }}"
                                       style="width: auto;"
                                       @checked(in_array((string) $subject->subject_id, $selectedSubjectIds, true))>
                                {{ $subject->subject_name }}
                            </label>
                        @empty
                            <p>ยังไม่มีรายวิชาในระบบ กรุณาติดต่อแอดมินก่อนส่งคำขอ</p>
                        @endforelse
                    </div>
                </div>

                <!-- ช่องทางการติดต่อ (Contact Table) -->
                <div class="pe-section">
                    <h2>ช่องทางการติดต่อ</h2>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" placeholder="เช่น mylineid"
                                   value="{{ old('line_id', $contact?->line_id ?? $tutorProfile?->line_id) }}">
                        </div>
                        <div class="pe-field">
                            <label for="discord_id">Discord ID</label>
                            <input type="text" id="discord_id" name="discord_id" placeholder="เช่น username#1234"
                                   value="{{ old('discord_id', $contact?->discord_id ?? $tutorProfile?->discord_id) }}">
                        </div>
                    </div>

                    <div class="pe-row" style="margin-top: 12px;">
                        <div class="pe-field">
                            <label for="zoom_link">Zoom Meeting Link</label>
                            <input type="text" id="zoom_link" name="zoom_link" placeholder="https://zoom.us/j/..."
                                   value="{{ old('zoom_link', $contact?->zoom_link ?? $tutorProfile?->zoom_link) }}">
                        </div>
                        <div class="pe-field">
                            <label for="google_meet_link">Google Meet Link</label>
                            <input type="text" id="google_meet_link" name="google_meet_link" placeholder="https://meet.google.com/..."
                                   value="{{ old('google_meet_link', $contact?->google_meet_link ?? $tutorProfile?->google_meet_link) }}">
                        </div>
                         <div class="pe-field">
                            <label for="meeting_location">สถานที่นัดหมาย</label>
                            <input type="text" id="meeting_location" name="meeting_location" placeholder="ระบุสถานที่นัดหมาย"
                                   value="{{ old('meeting_location', $contact?->meeting_location ?? $tutorProfile?->meeting_location) }}">
                        </div>
                    </div>
                </div>

                <!-- ปุ่มกด -->
                <div class="pe-actions">
                    <button type="submit" class="pe-save">{{ $isPending ? 'รอแอดมินอนุมัติ' : ($isApplication ? 'ยืนยันและส่งคำขอ' : 'บันทึกข้อมูล') }}</button>
                    <a href="javascript:history.back()" class="pe-cancel">ยกเลิก</a>
                </div>
                </fieldset>
            </form>
        </div>
    </main>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            // หากยังไม่ได้เลือกรูป ไม่ต้องเปลี่ยนภาพตัวอย่าง
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                document.getElementById('avatar-preview').src = reader.result;
            };
            reader.readAsDataURL(file);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // เปิดและปิดเมนูโปรไฟล์ตามเดิม
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
