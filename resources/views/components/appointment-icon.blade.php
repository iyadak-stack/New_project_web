@props(['name'])
{{-- ใช้ไอคอนเล็ก ๆ ร่วมกันในหน้านัดหมาย โดยไม่ต้องติดตั้งชุดไอคอน --}}
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @if ($name === 'user')
        <circle cx="12" cy="7" r="4" />
        <path d="M5 21v-3a7 7 0 0 1 14 0v3H5Z" />
    @elseif ($name === 'calendar')
        <rect x="4" y="5" width="16" height="16" rx="2" />
        <path d="M8 3v4M16 3v4M4 10h16" />
    @elseif ($name === 'clock')
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5l3 2" />
    @elseif ($name === 'screen')
        <rect x="3" y="4" width="18" height="13" rx="1" />
        <path d="M12 17v4M8 21h8" />
    @elseif ($name === 'video')
        <rect x="3" y="6" width="12" height="12" rx="2" />
        <path d="m15 10 6-3v10l-6-3" />
    @elseif ($name === 'book')
        <path d="M12 5v16M12 5C8 2 5 3 2 4v15c3-1 6-1 10 2M12 5c4-3 7-2 10-1v15c-3-1-6-1-10 2" />
    @elseif ($name === 'pin')
        <path d="M19 9c0 5-7 12-7 12S5 14 5 9a7 7 0 0 1 14 0Z" />
        <circle cx="12" cy="9" r="2" />
    @elseif ($name === 'check')
        <circle cx="12" cy="12" r="9" />
        <path d="m8 12 3 3 5-6" />
    @elseif ($name === 'link')
        <path d="m10 14 4-4M8 15l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0M16 9l1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" transform="translate(1 -1) scale(.9)" />
    @endif
</svg>
