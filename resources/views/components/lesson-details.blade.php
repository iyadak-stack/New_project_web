@props(['appointment'])
@php
$contact = $appointment->tutorProfile?->user?->contact;
$zoom = $appointment->lessonValue('meeting_zoom');
$meet = $appointment->lessonValue('meeting_meet');
$location = $appointment->lessonValue('meeting_location');
@endphp
<div class="alert alert-info mt-3">
    <strong>รายละเอียดการเรียน</strong>
    @if($zoom)
        <div>
            <a href="{{ $zoom }}" target="_blank" rel="noopener">เข้าห้องเรียน Zoom</a>
        </div>
    @endif
    @if($meet)
        <div>
            <a href="{{ $meet }}" target="_blank" rel="noopener">เข้าห้องเรียน Google Meet</a>
        </div>
    @endif
    @if($location)
        <div>
            สถานที่: {{ $location }}
        </div>
    @endif
    @if($contact?->line_id)
        <div>
            LINE: {{ $contact->line_id }}
        </div>
    @endif
    @if($appointment->lesson_notes)
        <p style="white-space:pre-wrap">{{ $appointment->lesson_notes }}</p>
    @endif
    @if(!$zoom && !$meet && !$location)
        <p>กรุณาติดต่อผู้สอนเพื่อรับรายละเอียดห้องเรียน</p>
    @endif
</div>
