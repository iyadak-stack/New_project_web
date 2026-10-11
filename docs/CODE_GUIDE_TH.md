# อ่านโค้ด PeerTutor จากตรงไหน

## โครงสร้างพื้นฐาน

1. `routes/web.php` รับ URL แล้วเลือก Controller
2. Controller รับข้อมูลฟอร์ม ตรวจข้อมูลและสิทธิ์ แล้วเรียก Model
3. Model อ่านหรือบันทึกข้อมูลในฐานข้อมูล และกำหนดความสัมพันธ์ระหว่างตาราง
4. ไฟล์ Blade ใน `resources/views` แสดงข้อมูลที่ Controller ส่งมา

โฟลเดอร์ `app/Domains` แยกตามงาน เช่น Auth, Booking, TutorProfile, Scheduling และ Reportreview ใช้โครงสร้างเดิมของโปรเจกต์ต่อได้เลย

## ส่วนสำคัญที่แก้ไว้

| งาน | ไฟล์เริ่มต้น |
| --- | --- |
| สมัครสมาชิก | `app/Domains/Auth/Actions/Fortify/CreateNewUser.php` |
| สลับนักเรียน/ติวเตอร์ | `app/Domains/Auth/Http/Controllers/RoleController.php` |
| ข้อมูลใบสมัครติวเตอร์ | `app/Domains/TutorProfile/Services/TutorApplication.php` |
| แอดมินตรวจใบสมัคร | `app/Domains/TutorProfile/Http/Controllers/AdminTutorController.php` |
| หน้าแรกและโปรไฟล์ติวเตอร์ | `app/Domains/TutorProfile/Http/Controllers/TutorController.php` |
| รายการโปรด | `app/Domains/TutorProfile/Http/Controllers/FavoriteController.php` |
| วันเวลาสอน | `app/Domains/Scheduling/Http/Controllers/AvailabilityController.php` |
| จอง รับคำขอ จบการเรียน และรีวิว | `app/Domains/Booking/Http/Controllers/AppointmentController.php` |
| แจ้งเตือน | `app/Domains/Booking/Http/Controllers/NotificationController.php` |
| รายงานและจัดการผู้ใช้ | `app/Domains/Reportreview/Http/Controllers/Admin/ReportController.php` |
| Navbar ร่วม | `resources/views/components/home-navbar.blade.php` |
| ฟอร์มติวเตอร์ร่วม | `resources/views/components/tutor-fields.blade.php` |

## ลำดับการจอง

- `store`: ตรวจติวเตอร์ วิชา และเวลาว่าง แล้วสร้างคำขอเป็น `pending`
- `updateStatus`: ติวเตอร์เจ้าของนัดหมายรับเป็น `confirmed` หรือปฏิเสธเป็น `cancelled`
- `updateLesson`: เพิ่ม แก้ หรือล้างข้อมูลห้องเรียนเฉพาะนัดหมาย
- `complete`: นักเรียนเจ้าของนัดหมายยืนยันเรียนจบได้หลังติวเตอร์รับ และผ่านเวลาสิ้นสุด จึงเปลี่ยนเป็น `completed`
- `storeReview`: บันทึกรีวิวได้ครั้งเดียวหลังเรียนจบ และแจ้งติวเตอร์

`Appointment::lessonValue()` เลือกข้อมูลห้องเรียน ถ้าไม่เคยปรับของนัดหมายจะใช้ข้อมูลที่มีอยู่หรือข้อมูลโปรไฟล์ หากล้างช่องเองจะคงค่าว่างไว้จนกว่าจะกดคืนค่าจากโปรไฟล์

## คำที่พบในโค้ด

- `validate`: ตรวจข้อมูลฟอร์มก่อนบันทึก
- `abort_unless`: หยุดคำขอถ้าเงื่อนไขไม่ผ่าน เช่น ไม่ใช่เจ้าของข้อมูล
- `with`: ดึงข้อมูลที่เกี่ยวข้องมาพร้อมกัน เช่น นัดหมายพร้อมวิชาและติวเตอร์
- `DB::transaction`: ถ้าบันทึกขั้นตอนใดผิดพลาด ให้ยกเลิกการบันทึกในชุดนั้น
- `lockForUpdate`: ให้คำขออื่นรอระหว่างบันทึกข้อมูลเดียวกัน ป้องกันการจองหรือรีวิวซ้ำ
- `?->`: อ่านข้อมูลต่อเมื่อมีข้อมูล ถ้าไม่มีจะได้ null
- `??`: ใช้ค่าด้านขวาเมื่อค่าด้านซ้ายเป็น null
- `old`: แสดงค่าที่ผู้ใช้กรอกไว้เมื่อฟอร์มตรวจไม่ผ่าน
- `@csrf`: ใส่รหัสป้องกันคำขอจากฟอร์มอื่นที่ไม่ได้รับอนุญาต

ชื่อคอลัมน์บางตัว เช่น `Appointment_id` และ `Tutor_profiles_tutor_id` เป็นชื่อเดิมของฐานข้อมูล จึงคงไว้ให้ข้อมูลเก่ายังใช้งานได้

## เข้าใช้งานแอดมินในเครื่องทดลอง

เข้าสู่ระบบด้วยบัญชีแอดมิน แล้วเปิด `/admin` ใบสมัครติวเตอร์อยู่ที่ `/admin/tutors`
บัญชีตัวอย่างที่สร้างไว้ก่อนหน้านี้คือ `admin@example.com` รหัสผ่าน `password` สำหรับทดลองในเครื่องเท่านั้น
