@extends('domains.scheduling.layout')

@section('title', 'จัดการเวลาว่าง')

@section('content')
    <div class="welcome-box">
        <h1>จัดการเวลาว่าง</h1>
        <p>เพิ่มช่วงเวลาที่คุณสะดวก เพื่อใช้ค้นหาเวลาว่างตรงกัน</p>
    </div>

    @if (session('success'))
        <p class="alert alert-success" role="status">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div class="pe-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="detail-card" method="POST" action="{{ route('availabilities.store') }}">
        @csrf
        <h2 class="card-title">เพิ่มเวลาว่าง</h2>
        <div class="form-group">
            <label for="start_datetime">เริ่ม</label>
            <input id="start_datetime" class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required>
        </div>
        <div class="form-group">
            <label for="end_datetime">สิ้นสุด</label>
            <input id="end_datetime" class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required>
        </div>
        <button class="pe-save" type="submit">เพิ่มเวลาว่าง</button>
    </form>

    <h2 class="card-title" style="margin-top: 32px; margin-bottom: 16px;">เวลาว่างของฉัน</h2>

    @forelse ($availabilities as $slot)
        <div class="detail-card mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px;">
            <p style="margin: 0; font-size: 16px; font-weight: 600; color: #1e293b;">
                📅 {{ $slot->start_datetime->format('d/m/Y H:i') }} – {{ $slot->end_datetime->format('d/m/Y H:i') }}
            </p>
            
            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- ปุ่มแก้ไข -->
                <a href="{{ route('availabilities.edit', $slot) }}" 
                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 16px; background-color: #FFF2EE; color: #E87A5D; border: 1px solid #FCD2C4; border-radius: 999px; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s;"
                   onmouseover="this.style.backgroundColor='#E87A5D'; this.style.color='#ffffff';"
                   onmouseout="this.style.backgroundColor='#FFF2EE'; this.style.color='#E87A5D';">
                    ✏️ แก้ไข
                </a>

                <!-- ปุ่มเปิด Pop-up ลบ -->
                <button type="button" 
                        onclick="openDeleteModal('{{ route('availabilities.destroy', $slot) }}')"
                        style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 16px; background-color: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; border-radius: 999px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#DC2626'; this.style.color='#ffffff';"
                        onmouseout="this.style.backgroundColor='#FEE2E2'; this.style.color='#DC2626';">
                    🗑️ ลบ
                </button>
            </div>
        </div>
    @empty
        <div class="detail-card"><p class="empty-text">ยังไม่มีเวลาว่าง</p></div>
    @endforelse

    <!-- Custom Modal Confirm Popup -->
    <div id="deleteConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center;">
        <div style="background: #FFFFFF; border-radius: 24px; padding: 32px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); animation: modalFadeIn 0.2s ease-out;">
            <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; color: #DC2626;">
                🗑️
            </div>
            <h3 style="margin: 0 0 8px; font-size: 20px; font-weight: 700; color: #0F172A;">ยืนยันการลบเวลาว่าง</h3>
            <p style="margin: 0 0 24px; font-size: 14px; color: #64748B;">คุณแน่ใจหรือไม่ว่าต้องการลบช่วงเวลาว่างนี้? ข้อมูลนี้จะไม่สามารถกู้คืนได้</p>
            
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" onclick="closeDeleteModal()" style="flex: 1; padding: 10px 20px; background: #F1F5F9; color: #475569; border: none; border-radius: 999px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        ยกเลิก
                    </button>
                    <button type="submit" style="flex: 1; padding: 10px 20px; background: #EF4444; color: #FFFFFF; border: none; border-radius: 999px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        ยืนยันลบ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        function openDeleteModal(actionUrl) {
            const modal = document.getElementById('deleteConfirmModal');
            const form = document.getElementById('deleteForm');
            form.action = actionUrl;
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            modal.style.display = 'none';
        }

        // ปิดเมื่อคลิกพื้นหลังด้านนอก
        window.onclick = function(event) {
            const modal = document.getElementById('deleteConfirmModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }
    </script>
@endsection