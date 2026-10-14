@props(['href', 'label', 'direction' => 'right'])

{{-- ลิงก์ข้อความพร้อมลูกศร ใช้ร่วมกันโดยไม่มีพื้นหลังหรือกรอบปุ่ม --}}
<a href="{{ $href }}" style="display: inline-flex; align-items: center; gap: 10px; color: #ed805b; font-size: 15px; font-weight: 400; text-decoration: none;">
    @if ($direction === 'left')
        <span aria-hidden="true" style="font-size: 16px;">←</span>
    @endif
    {{ $label }}
    @if ($direction === 'right')
        <span aria-hidden="true" style="font-size: 16px;">→</span>
    @endif
</a>
