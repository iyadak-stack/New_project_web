# 4. Appointment, Subject & Notification

## 4.1 ข้อมูลเข้าและข้อตกลงระหว่างทีม

ฟอร์ม tutor/show ส่ง subject_id, tutor_id, availability_id. AppointmentController ใช้ Auth::id() เป็นผู้เรียนและอ่านวันเวลาจาก Availability เอง. ตาราง appointments เก็บ Subject_subject_id, Tutor_profiles_tutor_id และ Student_profiles_student_id ซึ่งข้อมูลใหม่ใช้รหัส user แม้ชื่อคอลัมน์จะดูเหมือนรหัสโปรไฟล์; บางรายการเก่าใช้ student_profiles.id จึงมีโค้ดรองรับสองแบบ

## 4.2 การจองและเปลี่ยนสถานะ

1. POST /appointments → store() validate → ตรวจ tutor approved/active, ห้ามจองตัวเอง, วิชาอยู่กับติวเตอร์, availability เป็นของติวเตอร์และอยู่อนาคต
2. ล็อก TutorProfile ใน transaction แล้วตรวจ appointments ช่วงทับที่ไม่ cancelled → สร้าง Appointment status=pending → สร้าง Notification ให้ผู้สอน → กลับ tutor.show พร้อมข้อความส่งคำขอสำเร็จ
3. GET /appointments → index() โหลดข้อมูลที่บัญชีเป็นผู้เรียนหรือผู้สอน → index.blade.php; GET รายการเดียว → show() ตรวจเจ้าของ → show.blade.php
4. POST status หรือ confirm → updateStatus() ตรวจเจ้าของติวเตอร์ current_role=tutor และ approved; เปลี่ยนได้จาก pending เท่านั้น → confirmed หรือ cancelled พร้อมเหตุผลปฏิเสธ → แจ้งนักเรียน
5. การยกเลิกผ่าน cancel() อนุญาตเจ้าของนักเรียนหรือผู้สอนที่มีสิทธิ์ เฉพาะ pending/confirmed → แจ้งอีกฝ่าย
6. POST complete → ล็อกนัดหมาย ตรวจเจ้าของนักเรียนและ Appointment::canComplete() ซึ่งต้อง confirmed และผ่าน end_datetime → completed → แจ้งติวเตอร์ → กลับ index พร้อม review_appointment เพื่อเปิด modal

| สถานะ | ความหมาย | การใช้งานสำคัญ |
| --- | --- | --- |
| pending | ส่งคำขอแล้วรอติวเตอร์รับ | นักเรียนยังไม่เห็นห้องเรียน/ยืนยันจบไม่ได้ |
| confirmed | ติวเตอร์รับแล้ว | แสดงข้อมูลห้องเรียน; ยืนยันจบได้เมื่อเวลาจบผ่าน |
| completed | นักเรียนยืนยันจบแล้ว | สร้างรีวิวได้หนึ่งครั้ง |
| cancelled | ปฏิเสธหรือยกเลิก | ไม่กันช่องเวลาเหมือนนัดที่ยังใช้งาน |

index อ่านนัดหมายที่เกี่ยวกับบัญชีทั้งสองบทบาท ไม่ได้กรองรายชื่อทั้งหมดตาม current_role อย่างเดียว ปุ่มจัดการจึงต้องตรวจทั้งเจ้าของและ role อีกชั้น

## 4.3 รายละเอียดห้องเรียนและการแก้เฉพาะนัด

contacts เป็นข้อมูลเริ่มต้นของติวเตอร์ ส่วน appointments เก็บข้อมูลห้องเรียนเฉพาะครั้ง. ตอนสร้างนัดเก็บค่าติดต่อที่มี ณ ตอนนั้น; ถ้าของนัดว่างและยังไม่ปรับเอง lessonValue จึง fallback ไปโปรไฟล์ การแก้โปรไฟล์ภายหลังไม่จำเป็นต้องเปลี่ยนค่าที่ snapshot ลงนัดไว้แล้ว

show.blade.php → PATCH appointments.lesson → updateLesson() → บันทึก meeting_zoom/meeting_meet/meeting_location/lesson_notes และ lesson_details_customized=true. ล้างช่องแล้วบันทึกทำให้ว่างจริง ไม่ดึงค่าโปรไฟล์กลับเอง. ปุ่ม use_profile_defaults ล้างค่าของนัดและ flag เพื่อกลับไปใช้โปรไฟล์ (รวมล้าง lesson_notes ตามโค้ด)

student เห็น lesson-details เฉพาะ confirmed/completed. ลิงก์เป็นค่าที่ผู้สอนกรอก ไม่ได้สร้างห้อง Zoom/Meet ผ่าน API และระบบไม่ได้ตรวจว่าห้องเปิดอยู่จริง

## 4.4 วิชาและแจ้งเตือน

SubjectSeeder → subjects → Subject model → ฟอร์ม tutor-fields เลือกวิชา → TutorApplication sync ตารางกลาง → tutor/show เลือกเฉพาะวิชาของผู้สอน → store ตรวจซ้ำก่อนจอง. SubjectController มี CRUD บางส่วน แต่เส้นทาง resource มี action มากกว่าที่ implement อยู่

แจ้งเตือนการจองสร้างโดย AppointmentController; ใบสมัคร/อนุมัติใช้ UserNotification. NotificationController::index อ่านเฉพาะ Users_user_id ปัจจุบัน; markAsRead ตรวจเจ้าของแล้วตั้ง is_read. notification-nav-link นับที่ยังไม่อ่านและแสดง badge. เป็นการอ่านข้อมูลเมื่อโหลดหน้า ไม่ใช่ WebSocket, browser push, SMS หรืออีเมลอัตโนมัติ

## 4.5 ส่วนเพิ่ม เปลี่ยน และของเดิม

- เพิ่ม: หน้านัดหมาย/รายละเอียด ห้องเรียนเฉพาะนัด การยืนยันจบ modal รีวิว และแจ้งเตือนพร้อม badge
- เปลี่ยน: บังคับ pending ที่ server; ต้องรับและหมดเวลาเรียนก่อนยืนยันจบ; ปุ่ม Bootstrap ไม่ถูก .btn ของหน้า home ทับและใช้ align-items-start กันยืด
- เปลี่ยน: แบบจองหลักเลือก availability แทนวันอิสระ
- เก็บไว้แต่ไม่ตรง flow: appointments/create ยังมี route แต่ไม่ส่ง availability_id; ฟอร์ม tutor/show มี grade_level แต่ store ไม่บันทึกฟิลด์นี้ อย่าอธิบายว่าเก็บระดับชั้นครบแล้ว
- ยังขาด: SubjectController ไม่มี create/show/edit ตาม resource; AppointmentController ไม่มี destroy แม้ resource ลงทะเบียนไว้; NotificationController ไม่มี destroy แม้ route ลบยังอยู่ ต้องแยกจากปุ่ม/flow ที่ทำงานจริง

## 4.6 แนวพูดพรีเซนต์

“การกดจองคือส่งคำขอ ไม่ใช่ยืนยันเรียนทันที เราใช้สถานะควบคุมปุ่มและตรวจซ้ำที่ server ห้องเรียนแก้แยกตามนัดได้ และหลังเรียนจบจึงเปิดรีวิว ข้อความแจ้งเตือนเก็บในฐานข้อมูลเพื่อให้เจ้าของบัญชีกลับมาอ่านได้”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Booking/Http/Controllers/AppointmentController.php` | ศูนย์กลางนัดหมาย: list/show/store/updateStatus/updateLesson/complete/storeReview; ตรวจสิทธิ์และสถานะก่อนเขียนข้อมูล ส่งข้อความใน notifications | routes/web.php: /appointments/{appointment}/lesson → updateLesson(); /appointments/{id}/status → updateStatus(); /appointments/{appointment}/complete → complete(); /appointments/{appointment}/review → storeReview(); /appointments/{appointment}/confirm → confirm(); /appointments/{appointment}/cancel → cancel(); ใช้ Appointment.php, Notification.php, NotificationType.php, Subject.php, Review.php, Availability.php, StudentProfile.php, TutorProfile.php, create.blade.php, index.blade.php, show.blade.php | โค้ดในระบบ |
| `app/Domains/Booking/Http/Controllers/NotificationController.php` | แสดงแจ้งเตือนของผู้ใช้ปัจจุบัน และ markAsRead ตรวจเจ้าของก่อนบันทึก is_read; route ลบยังไม่มี method destroy | routes/web.php: /notifications → index(); /notifications/{id}/read → markAsRead(); /notifications/{notification} → destroy(); ใช้ Notification.php, index.blade.php | มีส่วนต้องเชื่อมให้ครบ |
| `app/Domains/Booking/Http/Controllers/SubjectController.php` | อ่านรายวิชา เพิ่ม แก้ ลบ subjects; resource route มีบาง action ที่ Controller ยังไม่มี และไม่ได้จำกัด CRUD เฉพาะ admin | เชื่อม Subject.php, index.blade.php | มีส่วนต้องเชื่อมให้ครบ |
| `app/Domains/Booking/Models/Appointment.php` | แทน appointments เชื่อมวิชา ผู้เรียน ติวเตอร์ รีวิว; lessonValue เลือกห้องเรียน และ canComplete ตรวจรับคำขอแล้ว/หมดเวลาเรียน | Controller/Service → Model → ตาราง appointments; ความสัมพันธ์ไป User.php, Review.php, StudentProfile.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Booking/Models/Notification.php` | ข้อความแจ้งเตือน ผู้รับ สถานะอ่าน และความสัมพันธ์ประเภทแจ้งเตือน | Controller/Service → Model → ตาราง notifications; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Booking/Models/NotificationType.php` | ประเภทของแจ้งเตือน เชื่อมหนึ่งประเภทกับหลายข้อความ | Controller/Service → Model → ตาราง notification_types; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Booking/Models/Subject.php` | ข้อมูลรายวิชา เชื่อมนัดหมายและติวเตอร์ผ่านตารางกลาง โดยรายชื่อติวเตอร์ใช้เงื่อนไข approved | Controller/Service → Model → ตาราง subjects; ความสัมพันธ์ไป TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Booking/Services/UserNotification.php` | ฟังก์ชันส่งแจ้งเตือนงานติวเตอร์ ใช้จากใบสมัครและการอนุมัติ; การจองมี notify ของตัวเองอีกชุด | เชื่อม Notification.php, NotificationType.php | โค้ดในระบบ |
| `database/migrations/2026_09_25_082910_create_subjects_table.php` | สร้าง subjects มี subject_id และ subject_name | artisan migrate → subjects → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082922_create_notification_types_table.php` | สร้างประเภทแจ้งเตือน notification_types | artisan migrate → notification_types → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082928_create_appointments_table.php` | สร้าง appointments สถานะ เวลา วิชา รหัสติวเตอร์/ผู้เรียน และ soft delete | artisan migrate → appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082932_create_notifications_table.php` | สร้างข้อความแจ้งเตือน ผู้รับ ประเภท และ is_read | artisan migrate → notifications → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163355_bridge_booking_schema.php` | เพิ่มคอลัมน์นัดหมาย/แจ้งเตือนที่ขาดให้ schema เดิม; down ลบคอลัมน์เหล่านี้ จึงต้องอ่านก่อน rollback | artisan migrate → appointments, notifications → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000003_add_lesson_override_flag.php` | เพิ่ม lesson_details_customized แยกการล้างข้อมูลนัดหมายเองออกจากยังไม่เคยตั้งค่า | artisan migrate → appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `public/CSS/notifications.css` | หน้าตารายการแจ้งเตือน | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: index.blade.php | โค้ดในระบบ |
| `public/CSS/subject-show.css` | หน้าตารายละเอียดวิชาเดิม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: show.blade.php | โค้ดในระบบ |
| `resources/views/components/lesson-details.blade.php` | อ่าน Appointment.lessonValue แล้วแสดง Zoom/Meet สถานที่ LINE และโน้ตตามที่หน้าหลักอนุญาต | รับจาก index.blade.php, show.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | โค้ดในระบบ |
| `resources/views/components/notification-nav-link.blade.php` | นับแจ้งเตือนยังไม่อ่านของผู้ใช้ แสดง badge; ไม่แสดงอะไรสำหรับ guest | รับจาก home-navbar.blade.php; ปุ่ม/ฟอร์มไป route notifications.index | โค้ดในระบบ |
| `resources/views/components/subject-card.blade.php` | การ์ดวิชาตัวอย่างใช้ Tailwind และปุ่มดูติวเตอร์ยังไม่ผูก route | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบตัวอย่าง |
| `resources/views/domains/booking/appointments/create.blade.php` | ฟอร์มจองเดิมเลือกวันเองและมีรหัส fallback แต่ไม่ส่ง availability_id ที่ store ปัจจุบันต้องการ | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route home, appointments.store, appointments.index | มี route แต่ไม่ตรงกับ flow หลัก |
| `resources/views/domains/booking/appointments/index.blade.php` | รายการนัดหมายของบัญชี ปุ่มรับ/ปฏิเสธ/ยืนยันจบ และ Bootstrap modal รีวิว; เปิด modal หลังจบหรือจาก hash link | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route appointments.show, appointments.complete, tutor.show, appointments.review, tutor.search; ใช้ home-navbar.blade.php, lesson-details.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/appointments/show.blade.php` | รายละเอียดนัดหมาย สถานะ ห้องเรียน ฟอร์มปรับเฉพาะนัดหมาย และปุ่มตามสิทธิ์ | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route appointments.index, appointments.lesson, appointments.complete, tutor.show; ใช้ home-navbar.blade.php, lesson-details.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/notifications/index.blade.php` | รายการแจ้งเตือนของผู้ใช้ กดอ่านผ่าน NotificationController และใช้ navbar ร่วม | รับจาก NotificationController.php; ปุ่ม/ฟอร์มไป route home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/subjects/index.blade.php` | หน้ารายวิชาพร้อมฟอร์มจัดการ เรียก SubjectController | รับจาก SubjectController.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ต้องทบทวนสิทธิ์ CRUD |
| `resources/views/domains/booking/subjects/show.blade.php` | หน้ารายละเอียดวิชาเดิม แต่ SubjectController ยังไม่มี show | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home, tutor.show | ยังเชื่อม route ไม่ครบ |

