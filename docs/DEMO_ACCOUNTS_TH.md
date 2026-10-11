# ข้อมูลตัวอย่างสำหรับลองเว็บ

รัน `php artisan migrate` แล้ว `php artisan db:seed` หรือ `php artisan db:seed --class=DemoWebsiteSeeder`

Seeder บัญชีทำงานเฉพาะ APP_ENV=local หรือ testing และใช้ firstOrCreate เพื่อไม่ล้างข้อมูลเดิมหรือรีเซ็ตรหัสผ่านที่แก้ไปแล้ว

บัญชีใหม่ในตารางนี้ใช้รหัสผ่าน `password` ทั้งหมด

| อีเมล | บทบาท / สิ่งที่ลองได้ |
| --- | --- |
| demo.admin@example.com | แอดมิน เปิด /admin ตรวจใบสมัคร รายงาน และปิดบัญชีพร้อมเหตุผล |
| demo.learner@example.com | นักเรียน จอง รายการโปรด ยืนยันเรียนจบ และรีวิว |
| demo.tutor@example.com | ติวเตอร์คณิตที่อนุมัติแล้ว รับคำขอ แก้ห้องเรียน ดูรีวิว |
| demo.english@example.com | ติวเตอร์อังกฤษที่อนุมัติแล้ว มีเวลาว่างสำหรับจอง |
| demo.pending@example.com | ติวเตอร์รอแอดมินตรวจ |
| demo.rejected@example.com | ติวเตอร์ไม่ผ่าน แก้ข้อมูลแล้วส่งใบสมัครใหม่ |
| demo.disabled@example.com | บัญชีถูกปิด เข้าสู่ระบบแล้วจะเห็นเหตุผล |

## ตัวอย่างนัดหมายของนักเรียน

- DEMOAPP001: รอติวเตอร์รับ
- DEMOAPP002: ยืนยันแล้ว วันเรียนอยู่ในอนาคต
- DEMOAPP003: เรียนจบและมีรีวิวแล้ว
- DEMOAPP004: เรียนจบแต่ยังไม่ได้รีวิว
- DEMOAPP005: ยกเลิกพร้อมเหตุผล
- DEMOAPP006: ติวเตอร์รับแล้วและผ่านเวลาสิ้นสุด สามารถกดยืนยันเรียนจบเพื่อเปิดฟอร์มรีวิว

เวลาตัวอย่างอิงวันที่สร้างข้อมูลครั้งแรก ลิงก์ Zoom/Meet เป็น example.com สำหรับแสดง UI ไม่ใช่ห้องเรียนจริง

มีข้อมูลในตารางงานหลักทั้งหมด: users, student_profiles, tutor_profiles, subjects, tutor_profiles_has_subjects, contacts, availabilities, appointments, reviews, favorites, notification_types, notifications, report_reasons, reports และ report_evidences

ตารางที่ระบบสร้างระหว่างใช้งาน เช่น sessions, cache, jobs, password_reset_tokens และ passkeys ให้ Laravel จัดการเอง
