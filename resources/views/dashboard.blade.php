<x-layouts::app :title="__('Dashboard')">
    <div class="w-full min-h-screen bg-[#FFF9F5] p-6 text-gray-800">
        {{-- Header / Welcome Banner --}}
        <div class="mb-6 rounded-2xl bg-orange-100 p-6 shadow-sm border border-orange-200">
            <h1 class="text-2xl font-bold text-orange-600">
                ยินดีต้อนรับคุณ <span class="text-gray-900">{{ auth()->user()->first_name ?? auth()->user()->name }}</span> สู่ PeerTutor
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                บทบาทปัจจุบัน: 
                <span class="font-semibold text-orange-600">
                    {{ auth()->user()->role == 'tutor' ? 'ติวเตอร์' : 'นักเรียน' }}
                </span>
            </p>
        </div>

        {{-- Content Area --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- ฝั่งซ้าย/กลาง: ส่วนค้นหาและเนื้อหาหลัก --}}
            <div class="md:col-span-2 space-y-6">
                {{-- Search Bar --}}
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-2">
                    <input type="text" placeholder="ค้นหาวิชา ติวเตอร์..." class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-orange-400">
                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition">
                        ค้นหา
                    </button>
                </div>

                {{-- Section รายการติวเตอร์ / คอร์สเรียน --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold mb-4">ติวเตอร์แนะนำ</h2>
                    <div class="text-gray-500 text-sm">
                        ยังไม่มีรายการติวเตอร์ในขณะนี้
                    </div>
                </div>
            </div>

            {{-- ฝั่งขวา: โปรไฟล์และการนัดหมาย --}}
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold mb-2">โปรไฟล์ของฉัน</h2>
                    <p class="text-sm text-gray-500 mb-4">ดูข้อมูลส่วนตัว และตารางนัดหมายของคุณ</p>
                    
                    <div class="border-t pt-4 space-y-2 text-sm">
                        <p><strong>ชื่อ-สกุล:</strong> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        <p><strong>อีเมล:</strong> {{ auth()->user()->email }}</p>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <h3 class="font-semibold text-sm mb-2">ตารางนัดหมาย</h3>
                        <div class="bg-gray-50 p-3 rounded-lg text-center text-xs text-gray-400">
                            ไม่มีรายการนัดหมายในขณะนี้
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>