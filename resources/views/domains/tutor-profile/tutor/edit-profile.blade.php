<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์ติวเตอร์ - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-profile-edit.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

@php
    $user = auth()->user();
    $avatarUrl = $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : ($user->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&color=e07a5f&background=fdeee8');

    // ดึง Contact ของติวเตอร์ (ตาราง Contact ใน ER)
    $contact = $user->contact;

    // ID ของวิชาที่ติวเตอร์เลือกไว้แล้ว (ตาราง Tutor_profiles_has_Subject)
    $selectedSubjectIds = collect(old('subjects', $tutorProfile?->subjects->pluck('subject_id')->toArray() ?? []))
        ->map(fn ($id) => (string) $id)
        ->values()
        ->all();

    // รายวิชาทั้งหมดส่งให้ JS อ่านผ่าน data-attribute
    $allSubjectsList = $allSubjects->map(fn ($s) => [
        'id'   => (string) ($s->subject_id ?? $s->id),
        'name' => $s->subject_name,
    ])->values()->all();
@endphp

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="pe-page">
        <div class="pe-card">

            <h1 class="pe-title">แก้ไขโปรไฟล์ติวเตอร์</h1>
            <p class="pe-sub">อัปเดตข้อมูลส่วนตัว ข้อมูลการสอน และช่องทางการติดต่อของคุณ</p>

            @if ($errors->any())
                <div class="pe-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tutor.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

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
                            <select id="teaching_mode" name="teaching_mode">
                                <option value="online" {{ old('teaching_mode', $tutorProfile?->teaching_mode) === 'online' ? 'selected' : '' }}>ออนไลน์ (Online)</option>
                                <option value="onsite" {{ old('teaching_mode', $tutorProfile?->teaching_mode) === 'onsite' ? 'selected' : '' }}>นัดเจอ (Onsite)</option>
                                <option value="both" {{ old('teaching_mode', $tutorProfile?->teaching_mode) === 'both' ? 'selected' : '' }}>ทั้งสองแบบ (Both)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- รายวิชาที่เปิดสอน (Tutor_profiles_has_Subject Table) -->
                <div class="pe-section">
                    <h2>วิชาที่เปิดสอน</h2>
                    <p class="pe-select-hint">เลือกรายวิชาที่คุณสะดวกสอนจากรายการด้านล่าง</p>

                    <div id="subject-data" hidden
                         data-initial="{{ json_encode($selectedSubjectIds) }}"
                         data-all="{{ json_encode($allSubjectsList) }}"></div>

                    <div id="selected-tags-container" class="pe-tags-box"></div>

                    <div class="pe-field" style="margin-top: 8px;">
                        <select id="subject-picker" class="pe-custom-select" onchange="addSubjectTag(this)">
                            <option value="">+ กดเพื่อเลือกวิชาที่สอน...</option>
                            @foreach ($allSubjects as $subject)
                                <option value="{{ $subject->subject_id ?? $subject->id }}" data-name="{{ $subject->subject_name }}">
                                    📘 {{ $subject->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="hidden-inputs-container"></div>
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
                    <button type="submit" class="pe-save">บันทึกข้อมูล</button>
                    <a href="javascript:history.back()" class="pe-cancel">ยกเลิก</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                document.getElementById('avatar-preview').src = reader.result;
            };
            reader.readAsDataURL(file);
        }

        const selectedSubjects = new Map();
        const dataEl = document.getElementById('subject-data');
        const initialSubjects = JSON.parse(dataEl.dataset.initial || '[]');
        const allSubjects = JSON.parse(dataEl.dataset.all || '[]');

        const allSubjectsMap = {};
        allSubjects.forEach(s => {
            allSubjectsMap[String(s.id)] = s.name;
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (Array.isArray(initialSubjects)) {
                initialSubjects.forEach(id => {
                    const key = String(id);
                    const name = allSubjectsMap[key] || key;
                    if (key) {
                        selectedSubjects.set(key, name);
                    }
                });
            }
            renderSubjectTags();

            // Script เปิด/ปิด Profile Card Dropdown
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

        function addSubjectTag(selectEl) {
            const value = selectEl.value;
            if (!value) return;

            const option = selectEl.options[selectEl.selectedIndex];
            const name = option.getAttribute('data-name');

            if (!selectedSubjects.has(String(value))) {
                selectedSubjects.set(String(value), name);
                renderSubjectTags();
            }

            selectEl.value = '';
        }

        function removeSubjectTag(id) {
            selectedSubjects.delete(String(id));
            renderSubjectTags();
        }

        function renderSubjectTags() {
            const tagsContainer = document.getElementById('selected-tags-container');
            const hiddenContainer = document.getElementById('hidden-inputs-container');

            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';

            if (selectedSubjects.size === 0) {
                const empty = document.createElement('span');
                empty.style.fontSize = '13px';
                empty.style.color = '#94a3b8';
                empty.textContent = 'ยังไม่ได้เลือกวิชา (กดเลือกวิชาจากช่องด้านล่าง)';
                tagsContainer.appendChild(empty);
                return;
            }

            selectedSubjects.forEach((name, id) => {
                const tag = document.createElement('div');
                tag.className = 'pe-tag';

                const label = document.createElement('span');
                label.textContent = '📘 ' + name;

                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', () => removeSubjectTag(id));

                tag.appendChild(label);
                tag.appendChild(removeBtn);
                tagsContainer.appendChild(tag);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'subjects[]';
                input.value = id;
                hiddenContainer.appendChild(input);
            });
        }
    </script>
</body>
</html>