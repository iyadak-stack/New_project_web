# 5. Review, Report & Admin Operation

## 5.1 รีวิวหลังเรียน

appointments/index.blade.php เปิด Bootstrap modal → POST appointments.review → AppointmentController::storeReview(). ฟังก์ชันล็อกนัดหมาย ตรวจเจ้าของนักเรียน status=completed และยังไม่มี review; validate rating 1–5/Comment; สร้าง Review ผ่านความสัมพันธ์ของนัด ใช้ Tutor_profiles_tutor_id จากนัดจริง ไม่เชื่อรหัสติวเตอร์จากฟอร์ม → คำนวณ average_rating → แจ้งติวเตอร์ → redirect กลับนัดหมาย

ผลแสดงที่ TutorController::profile() → dashboard.blade.php ส่วน reviews และ TutorController::show() → โปรไฟล์สาธารณะ. reviews.index/create เดิมเปลี่ยนเป็น redirect; reviews.store เดิมส่งต่อ storeReview เพื่อใช้กฎเดียวกัน

ReviewController::edit/update/destroy ยังใช้ Gate::authorize กับ ReviewPolicy อนุญาตเจ้าของหรือ current_role=admin. จุดแยกสำคัญ: update/destroy ปัจจุบันยังไม่ได้คำนวณ average_rating ใหม่เหมือนตอนสร้างรีวิว จึงอาจทำให้คะแนนที่เก็บกับรีวิวที่เหลือไม่ตรงกัน

## 5.2 รายงานติวเตอร์

โปรไฟล์ติวเตอร์ → GET reports.create?tutor_id=... → ReportController::create() โหลดติวเตอร์และ ReportReason → reports/create.blade.php → POST reports.store → ตรวจข้อมูล/ห้ามรายงานตัวเอง → Report status=pending

reports.Users_user_id เป็นผู้รายงาน; Reported_users_user_id เป็นผู้ถูกรายงาน; ReportReason_Reason_id เป็นเหตุผล. การแยกผู้รายงานกับผู้ถูกกล่าวถึงช่วยให้หน้า admin ไม่แสดงคนผิด

/admin/reports → index → รายการ; /admin/reports/{report} → show → รายละเอียด; PATCH → updateStatus → investigating/resolved/rejected. เป็นการเปลี่ยนสถานะรายงาน ไม่ได้ปิดบัญชีอัตโนมัติและยังไม่มีข้อความตอบกลับผู้รายงานอัตโนมัติใน handler นี้

## 5.3 แอดมินอนุมัติใบสมัคร

/admin/tutors → AdminTutorController::index → admin/tutors.blade.php ดูชื่อ ประวัติ วิชา ติดต่อ และสถานะ. ปุ่มอนุมัติ/ไม่อนุมัติส่ง PATCH → update(): ล็อกโปรไฟล์ ตรวจ pending และห้ามอนุมัติตนเอง ตรวจข้อมูลครบเมื่ออนุมัติ → บันทึก approval_status/reviewed_at/reviewed_by/reject_reason → UserNotification ส่งผลให้ผู้สมัคร

การตรวจเป็นการพิจารณาข้อมูลที่ผู้ใช้กรอก ยังไม่มีระบบตรวจบัตรประชาชนหรือเอกสารยืนยันตัวตนจากหน่วยงานภายนอก อย่าพรีเซนต์ว่าเป็นการยืนยันตัวบุคคลอัตโนมัติ

## 5.4 แอดมินจัดการบัญชี

/admin/users → ReportController::users() → admin/users/index.blade.php. ปิดบัญชีส่ง disabled_reason ที่บังคับกรอก → toggleUserStatus() ห้ามปิดบัญชี admin ที่กำลังใช้อยู่เอง → is_active=false + เหตุผล; เปิดคืน → true และล้างเหตุผล

ส่งผลไปสองหมวด: หมวด 1 ปฏิเสธ login/ออกจาก session พร้อมเหตุผล และหมวด 3 คัดติวเตอร์ที่ปิดบัญชีออกจากรายชื่อสาธารณะ. เข้าแอดมินผ่าน login ปกติและ /admin ใช้ role:admin; admin-navbar มี logout และลิงก์ไปงานแต่ละส่วน

## 5.5 ส่วนเพิ่ม เปลี่ยน และสิ่งที่ยังไม่มี

- เพิ่ม: Report/ReportReason model และผู้ถูกรายงาน, admin dashboard/รายงาน/ผู้ใช้/ใบสมัคร, เหตุผลปิดบัญชี, การแจ้งผลอนุมัติและรีวิวใหม่
- เปลี่ยน: รีวิวผูกกับนัดหมายจริงแทน hidden ID ตัวอย่าง; หน้าสร้างรีวิวหลักเป็น modal หลังยืนยันจบ
- เก็บไว้: reviews/create/index แบบเดิมยังเป็นไฟล์อยู่ แต่ Controller ปัจจุบันไม่ได้ใช้เป็นหน้าหลัก
- มีเพียงตาราง/ข้อมูลตัวอย่าง: report_evidences และไฟล์หลักฐานจาก Seeder; ยังไม่มี Model/Form/Controller อัปโหลดและหน้า admin ดูหลักฐานครบวงจร
- ไม่ได้อยู่ในระบบที่ทำแล้ว: การจ่ายเงิน คืนเงิน วิดีโอคอลในเว็บ การสร้างห้องประชุมจริง และการตรวจเอกสารตัวตน แม้มีคำเกี่ยวข้องในข้อมูลตัวอย่างหรือเหตุผลรายงาน

## 5.6 แนวพูดพรีเซนต์

“รีวิวเกิดจากนัดที่เรียนจบจริงและสร้างได้ครั้งเดียว แอดมินตรวจใบสมัครก่อนเปิดสิทธิ์ผู้สอน รับรายงาน และระบุเหตุผลเมื่อปิดบัญชี โดยเราแยกสถานะรายงาน สถานะบัญชี และสถานะใบสมัครออกจากกัน”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Reportreview/Http/Controllers/Admin/ReportController.php` | รับรายงานติวเตอร์และตรวจห้ามรายงานตัวเอง; ฝั่ง admin ดูสถิติ รายงาน ผู้ใช้ เปลี่ยนสถานะ และปิดบัญชีพร้อมเหตุผล | routes/web.php: /reports/create → create(); /reports → store(); / → dashboard(); /reports → index(); /reports/{report} → show(); /reports/{report} → updateStatus(); /users → users(); ดู flow ในหัวข้องานสำหรับ method อื่น; ใช้ User.php, Report.php, ReportReason.php, TutorProfile.php, dashboard.blade.php, index.blade.php, show.blade.php, index.blade.php, create.blade.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Http/Controllers/ReviewController.php` | เส้นทางรีวิวเดิมส่งการสร้างต่อ AppointmentController; index/create เปลี่ยนเป็น redirect; edit/update/delete ตรวจ Gate ก่อนดำเนินการ | routes/web.php: /reviews → index(); /reviews/create → create(); /reviews → store(); /reviews/{review}/edit → edit(); /reviews/{review} → update(); /reviews/{review} → destroy(); ใช้ AppointmentController.php, Appointment.php, Review.php, edit.blade.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/Report.php` | รายงานเชื่อมเหตุผล ผู้รายงาน ผู้ถูกรายงาน และโปรไฟล์ติวเตอร์ ทำให้ admin แยกทั้งสองฝ่ายได้ | Controller/Service → Model → ตาราง reports; ความสัมพันธ์ไป User.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/ReportReason.php` | รายการเหตุผลรายงาน ไม่มี timestamps; ส่งตัวเลือกให้ฟอร์มรายงาน | Controller/Service → Model → ตาราง report_reasons; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/Review.php` | รีวิวคะแนน 1–5 และ Comment ผูกนัดหมาย/ติวเตอร์ มี soft delete | Controller/Service → Model → ตาราง reviews; ความสัมพันธ์ไป Appointment.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Policies/ReviewPolicy.php` | กำหนดเจ้าของรีวิวหรือบทบาท admin แก้/ลบได้ ใช้ผ่าน Gate ใน ReviewController | เชื่อม User.php, Review.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/AdminTutorController.php` | แอดมินดูใบสมัคร เรียงรอตรวจก่อน อนุมัติหรือปฏิเสธพร้อมเหตุผล บันทึกผู้ตรวจและส่งแจ้งเตือน | routes/web.php: /tutors → index(); /tutors/{tutorProfile} → update(); ใช้ UserNotification.php, TutorProfile.php, tutors.blade.php | งาน 5 ร่วมกับงาน 3 |
| `database/migrations/2026_09_29_154823_create_reviews_table.php` | สร้าง reviews คะแนน Comment นัดหมายและติวเตอร์ พร้อม soft delete | artisan migrate → reviews → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163329_create_reports_table.php` | สร้าง reports ผู้รายงาน เหตุผล สถานะ และรายละเอียด; FK report_reasons อยู่ใน migration ที่เวลาถัดไป ต้องระวังติดตั้งใหม่บางฐานข้อมูล | artisan migrate → reports → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163335_create_report_reasons_table.php` | สร้าง report_reasons เก็บเหตุผลให้ผู้ใช้เลือก | artisan migrate → report_reasons → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163340_create_report_evidence_table.php` | สร้าง report_evidences เก็บ path ชนิดไฟล์และรหัสรายงาน; ยังไม่มี flow อัปโหลดในหน้าเว็บ | artisan migrate → report_evidences → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000001_add_report_target_to_reports_table.php` | เพิ่ม Reported_users_user_id แยกผู้ถูกรายงานออกจากผู้รายงาน | artisan migrate → reports → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000002_add_tutor_approval_and_lesson_details.php` | เพิ่มสถานะใบสมัคร ผู้ตรวจ เวลา/เหตุผลตรวจ สถานที่ใน contacts และห้องเรียน/เหตุผลปฏิเสธใน appointments | artisan migrate → tutor_profiles, contacts, appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `resources/views/components/admin-navbar.blade.php` | เมนู admin รายงาน ผู้ใช้ ใบสมัครพร้อมจำนวนรอตรวจ และ logout/login | รับจาก dashboard.blade.php, index.blade.php, show.blade.php; ปุ่ม/ฟอร์มไป route admin.dashboard, admin.reports.index, admin.users.index, admin.tutors.index, logout, login | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/dashboard.blade.php` | สรุปจำนวนรายงาน ผู้ใช้ และบัญชีปิดใช้งาน พร้อมลิงก์จัดการ | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.index, admin.users.index; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/reports/index.blade.php` | รายการรายงานพร้อมผู้เกี่ยวข้องและลิงก์ดูรายละเอียด | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.show; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/reports/show.blade.php` | รายละเอียดผู้รายงาน/ผู้ถูกรายงานและฟอร์มเปลี่ยนสถานะ; ยังไม่ได้แสดงไฟล์หลักฐาน | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.index, admin.reports.update; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/tutors.blade.php` | ดูใบสมัคร ประวัติ วิชา ข้อมูลติดต่อ และอนุมัติ/ปฏิเสธพร้อมเหตุผล | รับจาก AdminTutorController.php; ปุ่ม/ฟอร์มไป route admin.tutors.update; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/users/index.blade.php` | ตารางผู้ใช้ ปุ่มเปิด/ปิดบัญชี และช่องเหตุผลที่ผู้ถูกปิดจะเห็น | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.users.toggle-status; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/reports/create.blade.php` | ฟอร์มเหตุผลและรายละเอียดรายงานติวเตอร์ ส่ง reports.store; ยังไม่มีอัปโหลดหลักฐาน | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route tutor.show, reports.store | โค้ดในระบบ |
| `resources/views/domains/reportreview/reviews/create.blade.php` | ฟอร์มรีวิวเดิมมีรหัสตัวอย่างคงที่ ปัจจุบัน create redirect ไปนัดหมายเพื่อเลือกรีวิวที่ถูกต้อง | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route reviews.store | ไม่ใช้สร้างรีวิวหลัก |
| `resources/views/domains/reportreview/reviews/edit.blade.php` | ฟอร์มแก้คะแนน/ข้อความส่ง reviews.update หลังตรวจสิทธิ์ | รับจาก ReviewController.php; ปุ่ม/ฟอร์มไป route reviews.update, reviews.index | โค้ดในระบบ |
| `resources/views/domains/reportreview/reviews/index.blade.php` | หน้ารวมรีวิวเดิม ปัจจุบัน reviews.index redirect ไป dashboard/นัดหมาย | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route reviews.edit, reviews.destroy | ไม่ถูกเรียกโดย index ปัจจุบัน |

