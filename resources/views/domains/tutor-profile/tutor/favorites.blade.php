@extends('domains.tutor-profile.tutor.tutor')

@section('title', 'รายการโปรด')

@section('content')
<div style="max-width: 1140px; margin: 24px auto; padding: 0 16px; font-family: 'Prompt', sans-serif;">

    @if (session('success'))
        <div style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">❤️ รายการโปรด</h1>
        <p style="font-size: 14px; color: #64748b; margin: 0;">รายการติวเตอร์ที่คุณกดบันทึกไว้</p>
    </div>

    @if ($favorites && $favorites->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            @foreach ($favorites as $favorite)
                @php
                    $tutor = $favorite->tutorProfile;
                @endphp

                @if ($tutor)
                    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #f1f5f9; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <!-- Tutor Header Profile -->
                            <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: #fca5a5; flex-shrink: 0; border: 2px solid #e07a5f;">
                                    @if ($tutor->user?->profile_picture)
                                        <img src="{{ asset('storage/' . $tutor->user->profile_picture) }}" alt="Tutor" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; color: #fff; font-size: 20px; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                                            {{ mb_substr($tutor->user?->first_name ?? 'T', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                        พี่{{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }}
                                    </h2>
                                    <div style="font-size: 13px; color: #e07a5f; font-weight: 600;">
                                        ⭐ {{ number_format($tutor->average_rating ?? 5.0, 1) }} / 5.0
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                <div><strong>ประสบการณ์:</strong> {{ $tutor->experience_years ?? 0 }} ปี</div>
                                <div>
                                    <strong>รูปแบบการสอน:</strong>
                                    @if ($tutor->teaching_mode === 'online')
                                        💻 ออนไลน์
                                    @elseif ($tutor->teaching_mode === 'onsite')
                                        📍 นัดเจอ (Onsite)
                                    @else
                                        🔄 ออนไลน์ & Onsite
                                    @endif
                                </div>
                                <div style="color: #64748b; font-size: 13px; line-height: 1.5; background: #f8fafc; padding: 10px; border-radius: 8px;">
                                    {{ \Illuminate\Support\Str::limit($tutor->bio ?? 'ยังไม่มีข้อมูลแนะนำตัว', 90) }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions Buttons -->
                        <div style="display: flex; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                            <a href="{{ route('tutor.show', $tutor->tutor_id) }}"
                               style="flex: 1; text-align: center; background: #e07a5f; color: #ffffff; padding: 10px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">
                                ดูโปรไฟล์
                            </a>

                            <form action="{{ route('tutor.favorite.destroy', $tutor->tutor_id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit"
                                        style="background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; padding: 10px 14px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                                        onclick="return confirm('ยืนยันลบออกจากรายการโปรด?')"
                                        title="ลบออกจากรายการโปรด">
                                    🗑️ ลบ
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 20px; text-align: center; margin-top: 20px;">
            <div style="font-size: 40px; margin-bottom: 12px;">🤍</div>
            <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">ยังไม่มีติวเตอร์ในรายการโปรด</h2>
            <p style="font-size: 14px; color: #64748b; margin: 0 0 20px 0;">ลองค้นหาและเพิ่มติวเตอร์ที่สนใจเข้ามาไว้ในรายการโปรดได้ง่ายๆ</p>

            <a href="{{ route('tutor.search') }}"
               style="display: inline-block; background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600;">
                🔍 ค้นหาติวเตอร์
            </a>
        </div>
    @endif

</div>
@endsection