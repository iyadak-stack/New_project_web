<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์นักเรียน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
</head>
<body>

@php
    $user = auth()->user();
    $avatarUrl = $user?->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : ($user?->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode(($user?->first_name ?? 'Student') . ' ' . ($user?->last_name ?? '')) . '&color=e07a5f&background=fdeee8');

    // ดึงข้อมูล Contact ของผู้ใช้
    $contact = $user?->contact;

    // ดึง ID วิชาที่เคยเลือกไว้
    $existingSubjectIds = $studentProfile?->subjects?->pluck('subject_id')?->toArray() ?? [];
    
    $selectedSubjectIds = collect(old('subjects', $existingSubjectIds))
        ->map(fn ($id) => (string) $id)
        ->filter()
        ->values()
        ->all();

    // รายวิชาทั้งหมดส่งให้ JS อ่านผ่าน data-attribute
    $allSubjectsList = collect($allSubjects ?? [])->map(fn ($s) => [
        'id'   => (string) ($s->subject_id ?? $s->id),
        'name' => $s->subject_name,
    ])->values()->all();
@endphp

    <!-- เรียกใช้งาน Dynamic Navbar ตาม Session Role -->
    <x-navbar />

    <main class="pe-page">
        <div class="pe-card">

            <h1 class="pe-title">แก้ไขโปรไฟล์นักเรียน</h1>
            <p class="pe-sub">อัปเดตข้อมูลประวัติส่วนตัว วิชาที่สนใจ และช่องทางการติดต่อของคุณ</p>

            @if ($errors->any())
                <div class="pe-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- รูปโปรไฟล์ -->
                <div class="pe-avatar-wrap">
                    <div class="pe-avatar">
                        <img id="avatar-preview" src="{{ $avatarUrl }}" alt="Profile Image">
                    </div>

                    <label for="avatar" class="pe-avatar-btn">เปลี่ยนรูปโปรไฟล์</label>
                    <input type="file" id="avatar" name="avatar" style="display: none;" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- ข้อมูลส่วนตัว -->
                <div class="pe-section">
                    <h2>ข้อมูลส่วนตัว</h2>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="first_name">ชื่อ</label>
                            <input type="text" id="first_name" name="first_name"
                                   value="{{ old('first_name', $user?->first_name) }}" required>
                        </div>
                        <div class="pe-field">
                            <label for="last_name">นามสกุล</label>
                            <input type="text" id="last_name" name="last_name"
                                   value="{{ old('last_name', $user?->last_name) }}" required>
                        </div>
                    </div>

                    <div class="pe-field" style="margin-top: 16px;">
                        <label for="bio">ประวัติส่วนตัว / เป้าหมายการเรียน (Bio)</label>
                        <textarea id="bio" name="bio" rows="3"
                                  placeholder="ระบุเกี่ยวกับตัวคุณ เช่น สไตล์การเรียน หรือเป้าหมายที่ต้องการเรียนรู้">{{ old('bio', $studentProfile?->bio) }}</textarea>
                    </div>
                </div>

                <!-- วิชาที่สนใจเรียน -->
                <div class="pe-section">
                    <h2>วิชาที่สนใจอยากเรียน</h2>
                    <p class="pe-select-hint">เลือกรายวิชาที่คุณสนใจอยากเรียนรู้เพิ่มเติม</p>

                    <div id="subject-data" hidden
                         data-initial="{{ json_encode($selectedSubjectIds) }}"
                         data-all="{{ json_encode($allSubjectsList) }}"></div>

                    <div id="selected-tags-container" class="pe-tags-box"></div>

                    <div class="pe-field" style="margin-top: 8px;">
                        <select id="subject-picker" class="pe-custom-select" onchange="addSubjectTag(this)">
                            <option value="">+ กดเพื่อเลือกวิชาที่สนใจ...</option>
                            @if(isset($allSubjects))
                                @foreach ($allSubjects as $subject)
                                    <option value="{{ $subject->subject_id ?? $subject->id }}" data-name="{{ $subject->subject_name }}">
                                        📘 {{ $subject->subject_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div id="hidden-inputs-container"></div>
                </div>

                <!-- ช่องทางการติดต่อ -->
                <div class="pe-section">
                    <h2>ช่องทางการติดต่อ</h2>

                    <div class="pe-row">
                        <div class="pe-field">
                            <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" placeholder="เช่น mylineid"
                                   value="{{ old('line_id', $contact?->line_id) }}">
                        </div>
                        <div class="pe-field">
                            <label for="discord_id">Discord ID</label>
                            <input type="text" id="discord_id" name="discord_id" placeholder="เช่น username#1234"
                                   value="{{ old('discord_id', $contact?->discord_id) }}">
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

            // Script ควบคุม Dropdown ของ Profile Card
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

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '✕';
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