@props(['profile' => null, 'contact' => null])
@php
    // เตรียมรายการตัวเลือกก่อนแสดงฟอร์ม จะได้ไม่เขียนเงื่อนไขยาวใน HTML
    $teachingModes = [
        'online' => 'ออนไลน์',
        'onsite' => 'พบกัน',
        'both' => 'ออนไลน์และพบกัน',
    ];
    $contactLabels = [
        'line_id' => 'LINE ID',
        'discord_id' => 'Discord',
        'zoom_link' => 'ลิงก์ Zoom เริ่มต้น',
        'google_meet_link' => 'ลิงก์ Google Meet เริ่มต้น',
        'meeting_location' => 'สถานที่เรียนเริ่มต้น',
    ];
    $subjects = \App\Domains\Booking\Models\Subject::all();
    $selectedSubjects = old('subjects', $profile?->subjects->pluck('subject_id')->all() ?? []);
@endphp
<div class="tutor-application-fields">
    <label class="form-label d-block">
        รูปโปรไฟล์ (ไม่บังคับ)
        <input class="form-control form-input" type="file" name="avatar" accept="image/jpeg,image/png,image/gif">
    </label>
    <label class="form-label d-block">
        แนะนำตัวและแนวทางการสอน
        <textarea class="form-control form-input" name="bio" required maxlength="3000" rows="4">{{ old('bio', $profile?->bio) }}</textarea>
    </label>
    <label class="form-label d-block">
        ประสบการณ์สอน (ปี)
        <input class="form-control form-input" name="experience_years" type="number" min="0" max="80" required value="{{ old('experience_years', $profile?->experience_years ?? 0) }}">
    </label>
    <label class="form-label d-block">
        รูปแบบการสอน
        <select class="form-select form-input" name="teaching_mode" required>
            @foreach($teachingModes as $value => $label)
                <option value="{{ $value }}" @selected(old('teaching_mode', $profile?->teaching_mode)===$value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <fieldset class="my-3">
        <legend class="fs-6">วิชาที่สอน (อย่างน้อย 1 วิชา)</legend>
        @foreach($subjects as $subject)
            <label style="display:block">
                <input type="checkbox" name="subjects[]" value="{{ $subject->subject_id }}" @checked(in_array($subject->subject_id, $selectedSubjects))> {{ $subject->subject_name }}
            </label>
        @endforeach
    </fieldset>
    @foreach($contactLabels as $field => $label)
        <label class="form-label d-block">
            {{ $label }} (ไม่บังคับ)
            <input class="form-control form-input" name="{{ $field }}" type="{{ in_array($field,['zoom_link','google_meet_link']) ? 'url' : 'text' }}" value="{{ old($field, $contact?->$field) }}">
        </label>
    @endforeach
    <p class="text-secondary small">ข้อมูลห้องเรียนเป็นค่าเริ่มต้น และปรับแยกตามนัดหมายได้เมื่อได้รับอนุมัติเป็นติวเตอร์</p>
</div>
