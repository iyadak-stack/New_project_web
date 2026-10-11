# 3. Tutor Profiles, Search & Ranking

## 3.1 หน้าที่

ให้ผู้เรียนเห็นว่าใครสอนอะไร มีประสบการณ์เท่าไร รีวิวเป็นอย่างไร และว่างเมื่อไร ไฟล์แกนคือ TutorController, TutorProfile, FavoriteController และชุด Blade tutor-profile. ข้อมูลผู้ใช้มาจาก users, วิชามาจาก subjects ผ่าน tutor_profiles_has_subjects, คะแนนและความเห็นมาจาก reviews

## 3.2 Home ค้นหา และจัดอันดับ

- GET / → routes/web.php → TutorController::home() เมื่อเป็น guest/student → TutorProfile::approved() โหลด user/subjects จำนวน reviews → เรียง average_rating มากก่อน แล้ว experience_years → welcome หรือ student/student
- home ตัด Users_user_id ที่ตรงกับบัญชีตนเอง เพื่อไม่แสดงการ์ดตัวเอง; อย่าเหมารวมว่าทุกหน้าค้นหา/อันดับตัดตัวเองด้วย
- GET /tutors?search=คำค้น → search() ค้นชื่อ นามสกุล หรือชื่อวิชา → search.blade.php. ไม่ใช่ระบบค้นด้วย AI
- GET /tutors/ranking → ranking() → ranking.blade.php ใช้คะแนนเฉลี่ยและประสบการณ์เป็นเกณฑ์ ไม่ใช่จำนวนผู้ติดตาม/ยอดจอง
- GET /tutors/{tutorProfile} → show() ตรวจ approved/active หรือเป็นเจ้าของ → โหลดวิชา รีวิวและเวลาที่จองได้ → show.blade.php

ทั้งสามหน้าสาธารณะเปิดโดยไม่สมัครได้ แต่ปุ่มที่ทำให้เกิดข้อมูลใหม่ เช่น จอง/โปรด/รายงาน ยังส่งเข้าทาง auth

## 3.3 โปรไฟล์ผู้สอนและการอนุมัติ

editProfile() → edit-profile.blade.php → tutor-fields.blade.php → updateProfile() → TutorApplication::save(). ฟอร์มร่วมกับหน้าสมัครใช้กฎเดียวกัน ลดกรณีสมัครแล้วข้อมูลติวเตอร์ว่าง

บันทึก bio/experience/teaching_mode ใน tutor_profiles, subjects ในตารางกลาง, ลิงก์/สถานที่ใน contacts, รูปใน public disk และ path ใน users.profile_picture. หากยังไม่ approved จะเข้าสู่ pending แล้วให้หมวด 5 ตรวจ; หากผ่านอยู่แล้ว การแก้โปรไฟล์ทั่วไปยังคง approved ตามโค้ดปัจจุบัน

profile() → dashboard.blade.php แสดง pending/confirmed ของตน เวลาว่างข้างหน้าและรีวิว; เมนูจัดการอยู่ home-navbar สำหรับ tutor ที่ approved

## 3.4 รายการโปรดและโปรไฟล์นักเรียน

ปุ่มหัวใจ → POST tutor.favorite.store → FavoriteController ตรวจอนุมัติ แล้วหาแถวเดิมรวม soft delete; ถ้ามีให้ restore ถ้าไม่มีให้ create → redirect กลับพร้อมข้อความ. ปุ่มเอาออกใช้ destroyTutor ลบเฉพาะ user ปัจจุบัน. หน้า tutor.favorites โหลดรายการของบัญชีและกรองติวเตอร์ที่ approved/active

StudentProfileController แยกดู/แก้นักเรียน: ชื่อกับรูปบันทึก users; bio บันทึก student_profiles. รูปโหลด asset('storage/...') โดย web.php มี fallback อ่าน public disk เมื่อเครื่อง Windows ไม่สร้าง storage link

## 3.5 ส่วนเพิ่ม เปลี่ยน และส่วนเดิม

- เพิ่ม: ฟอร์มติวเตอร์ร่วม, หน้า application-status, dashboard ที่ใช้ข้อมูลจริง, รายการโปรด, รูปจาก public disk และ route fallback
- เปลี่ยน: home แสดงติวเตอร์จาก DB และไม่แสดงตัวเอง; รายชื่อสาธารณะเฉพาะ approved/active; guest ดูข้อมูลได้; ช่องค้นหาออกจาก navbar แต่ยังมีแบบฟอร์มค้นหาในหน้าค้นหา
- ย้าย: ปุ่มจัดการโปรไฟล์/วิชา เวลาว่าง และคำขอ ไป navbar เฉพาะ tutor; แจ้งเตือนแสดงเฉพาะผู้ล็อกอิน
- เก็บไว้: tutor/profile.blade.php เดิมไม่ได้เป็นหน้า profile หลักแล้ว; tutor/tutor.blade.php ยังเป็น layout ของ ranking/บาง profile และมี dashboard ตัวอย่างคงที่ใน fallback
- ข้อจำกัดที่ต้องรู้: total_teaching_seconds มีใน DB แต่ complete ปัจจุบันไม่ได้เพิ่มชั่วโมงสะสม จึงไม่ควรพรีเซนต์ว่าเป็นสถิติชั่วโมงที่อัปเดตครบแล้ว

## 3.6 แนวพูดพรีเซนต์

“เราคัดติวเตอร์ที่ผ่านการอนุมัติและยังเปิดบัญชีมาแสดง ค้นได้จากชื่อและวิชา จัดอันดับจากคะแนนเฉลี่ย และใช้ตารางกลางให้หนึ่งติวเตอร์สอนได้หลายวิชา ส่วนรายการโปรดเก็บแยกตามสมาชิก”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/TutorProfile/Http/Controllers/FavoriteController.php` | เพิ่ม คืนรายการเดิม หรือลบรายการโปรดของบัญชีปัจจุบัน และแสดงติวเตอร์ที่ยังอนุมัติ/ใช้งานได้ | routes/web.php: /tutor/{tutorProfile}/favorite → storeTutor(); /tutor/{tutorProfile}/favorite/remove → destroyTutor(); /tutors/favorites → tutorFavorites(); ใช้ Favorite.php, TutorProfile.php, favorites.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/StudentProfileController.php` | แสดง/แก้โปรไฟล์นักเรียน บันทึกชื่อใน users ประวัติใน student_profiles และรูปลง public disk | routes/web.php: /student/profile → profile(); /student/profile/edit → editProfile(); /student/profile → updateProfile(); ใช้ StudentProfile.php, edit-profile.blade.php, profile.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/TutorController.php` | home/search/ranking/show อ่านติวเตอร์ที่อนุมัติและบัญชีเปิดใช้งาน; profile แสดง dashboard; edit/updateProfile ใช้ TutorApplication; show ตัดเวลาที่ถูกจองออก | routes/web.php: /tutor/application-status → applicationStatus(); /tutor/onboarding → editProfile(); /tutor/onboarding → updateProfile(); /tutor/profile → profile(); /tutor/profile/edit → editProfile(); /tutor/profile → updateProfile(); /tutors → search(); ดู flow ในหัวข้องานสำหรับ method อื่น; ใช้ Contact.php, Appointment.php, Subject.php, Availability.php, Favorite.php, TutorProfile.php, TutorApplication.php, application-status.blade.php, dashboard.blade.php, edit-profile.blade.php, ranking.blade.php, search.blade.php, show.blade.php, welcome.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/Favorite.php` | เชื่อมผู้ใช้ ติวเตอร์ และวิชาใน favorites ลบแบบ soft delete เพื่อคืนรายการเดิมได้ | Controller/Service → Model → ตาราง favorites; ความสัมพันธ์ไป User.php, Subject.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/StudentProfile.php` | โปรไฟล์นักเรียนผูก user_id และเก็บ bio; มี HasUuids ใน Model ต้องระวังเส้นทางสร้างที่ไม่ได้ระบุ id 10 ตัวเอง | Controller/Service → Model → ตาราง student_profiles; ความสัมพันธ์ไป User.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/TutorProfile.php` | โปรไฟล์ติวเตอร์ เชื่อม User วิชา นัดหมาย รีวิว; approved คัดผู้พร้อมสอน และ isComplete ตรวจข้อมูลขั้นต่ำ | Controller/Service → Model → ตาราง tutor_profiles; ความสัมพันธ์ไป User.php, Appointment.php, Subject.php, Review.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Services/TutorApplication.php` | กฎและขั้นตอนสมัครติวเตอร์ร่วมสำหรับสมัครใหม่/สลับบทบาท/แก้โปรไฟล์ บันทึกวิชา ติดต่อ รูป และแจ้งแอดมินเมื่อรอตรวจ | เชื่อม Contact.php, User.php, UserNotification.php, TutorProfile.php | โค้ดในระบบ |
| `database/migrations/2026_09_14_140329_create_tutor_profiles_table.php` | สร้าง tutor_profiles ใช้ tutor_id และ Users_user_id ไม่ใช่ id/user_id แบบที่โค้ด scheduling เก่าบางตัวใช้ | artisan migrate → tutor_profiles → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_14_140446_create_favorites_table.php` | สร้าง favorites ผูก user/tutor/subject มี deleted_at สำหรับ soft delete | artisan migrate → favorites → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_18_032630_create_tutor_profiles_has_subject_table.php` | สร้างตารางกลางติวเตอร์–วิชา หนึ่งคนสอนได้หลายวิชา หนึ่งวิชามีหลายคน | artisan migrate → tutor_profiles_has_subjects → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_22_163929_create_student_profiles_table.php` | สร้าง student_profiles ใช้ id 10 ตัวและ user_id ไม่ซ้ำ | artisan migrate → student_profiles → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `public/CSS/edit-profile.css` | จัดหน้าฟอร์มแก้โปรไฟล์นักเรียน | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: edit-profile.blade.php | โค้ดในระบบ |
| `public/CSS/home.css` | หน้าตาหลัก การ์ด ปุ่ม home-card-button และ navbar; แยกจาก .btn เพื่อไม่ทับ Bootstrap | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor-page.blade.php, create.blade.php, index.blade.php, show.blade.php, index.blade.php, show.blade.php | โค้ดในระบบ |
| `public/CSS/profile-card.css` | กรอบเมนูบัญชี รูปโปรไฟล์ และปุ่มสลับบทบาท | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor-page.blade.php, index.blade.php, show.blade.php, index.blade.php, edit-profile.blade.php, student.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-dashboard.css` | โครง dashboard/layout ติวเตอร์เดิม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-detail.css` | หน้าโปรไฟล์สาธารณะ การ์ดวิชา รีวิว และพื้นที่จอง | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: show.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-navbar.css` | หน้าตา navbar ติวเตอร์เดิม บางหน้ายังโหลดไฟล์ | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: create.blade.php, index.blade.php, show.blade.php, favorites.blade.php, search.blade.php, show.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-profile-edit.css` | จัดฟอร์มแก้โปรไฟล์ติวเตอร์ | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: ตรวจการโหลดตาม layout ของหน้า | โค้ดในระบบ |
| `public/CSS/tutor-ranking.css` | อันดับและการ์ดติวเตอร์ยอดนิยม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: ranking.blade.php, tutor.blade.php | โค้ดในระบบ |
| `resources/views/components/tutor-card.blade.php` | แสดงข้อมูลการ์ดติวเตอร์ รูป ชื่อ คะแนน รีวิว วิชา และลิงก์ tutor.show | รับจาก student.blade.php, welcome.blade.php; ปุ่ม/ฟอร์มไป route tutor.show | โค้ดในระบบ |
| `resources/views/components/tutor-fields.blade.php` | ฟอร์มติวเตอร์ร่วม: bio ประสบการณ์ รูปแบบ วิชา รูป และข้อมูลติดต่อ/ห้องเรียนเริ่มต้น | รับจาก tutor-registration-fields.blade.php, edit-profile.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | โค้ดในระบบ |
| `resources/views/components/tutor-navbar.blade.php` | navbar ติวเตอร์เดิม ก่อนย้ายหน้าหลักไปใช้ home-navbar | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home, tutor.search, tutor.ranking, tutor.favorites, appointments.index, admin.dashboard, notifications.index, role.switch, profile.edit, login | ส่วนประกอบเดิม ไม่พบการเรียกจากหน้าหลัก |
| `resources/views/components/tutor-page.blade.php` | โครงหน้าติเตอร์แบบใหม่ ใช้ navbar ร่วม Bootstrap ข้อความสำเร็จ/ผิดพลาด และ slot เนื้อหา | รับจาก edit.blade.php, index.blade.php, application-status.blade.php; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/edit-profile.blade.php` | ฟอร์มชื่อ ประวัติ รูปนักเรียน ส่ง student.profile.update | รับจาก StudentProfileController.php; ปุ่ม/ฟอร์มไป route student.profile.update, tutor.profile, home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/profile.blade.php` | หน้าดูโปรไฟล์นักเรียนจาก StudentProfileController และสืบทอด layout tutor เดิม | รับจาก StudentProfileController.php; ปุ่ม/ฟอร์มไป route student.profile.edit; ใช้ tutor.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/student.blade.php` | หน้า home สำหรับผู้ล็อกอิน แสดงติวเตอร์/วิชายอดนิยมและ navbar ร่วม | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route tutor.search; ใช้ home-navbar.blade.php, tutor-card.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/application-status.blade.php` | หน้ารออนุมัติ/ไม่ผ่าน/ผ่านแล้ว พร้อมส่งแก้ใบสมัครหรือสลับบทบาท | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route role.switch, tutor.onboarding; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/dashboard.blade.php` | หน้าหลักติวเตอร์ปัจจุบัน: จำนวนคำขอ นัดหมาย เวลาสอน รีวิว และลิงก์จัดการ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route appointments.show; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/edit-profile.blade.php` | ฟอร์มสมัคร/แก้ติวเตอร์ ใช้ tutor-fields และส่ง onboarding หรือ profile.update ตามสถานะ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.onboarding.store, tutor.profile.update, home; ใช้ tutor-fields.blade.php, tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/favorites.blade.php` | รายการติวเตอร์โปรดของผู้ใช้ มี navbar ร่วมและลิงก์ไปโปรไฟล์ | รับจาก FavoriteController.php; ปุ่ม/ฟอร์มไป route tutor.show, tutor.favorite.destroy, tutor.search; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/profile.blade.php` | หน้าดูโปรไฟล์ติวเตอร์เดิม แต่ profile action ปัจจุบันใช้ dashboard แทน | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route tutor.profile.edit; ใช้ tutor.blade.php | ไม่ใช่หน้าหลักปัจจุบัน |
| `resources/views/domains/tutor-profile/tutor/ranking.blade.php` | แสดงลำดับติวเตอร์ตามข้อมูลที่ ranking ส่งมา และปุ่มดูโปรไฟล์/รายการโปรด | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.show, tutor.favorite.destroy, tutor.favorite.store, tutor.search; ใช้ tutor.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/search.blade.php` | ฟอร์มค้นหาติวเตอร์และรายวิชา พร้อมการ์ดผลลัพธ์ กดต่อไปโปรไฟล์สาธารณะ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.search, tutor.show, home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/show.blade.php` | โปรไฟล์สาธารณะ วิชา รีวิว เวลาที่เปิดสอน และฟอร์มเลือก availability_id เพื่อส่งคำขอจอง | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route home, tutor.favorite.destroy, tutor.favorite.store, reports.create, login, appointments.store; ใช้ home-navbar.blade.php, login-modal.blade.php, register-modal.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/tutor.blade.php` | layout เดิมยังใช้กับ ranking และหน้า profile บางหน้า; ส่วน dashboard สำรองในไฟล์มีข้อมูลคงที่ | รับจาก profile.blade.php, profile.blade.php, ranking.blade.php; ปุ่ม/ฟอร์มไป route appointments.index, notifications.index; ใช้ home-navbar.blade.php | layout ยังใช้ แต่ dashboard สำรองเป็นตัวอย่าง |
| `resources/views/welcome.blade.php` | home ของ guest ดูติวเตอร์และวิชา เปิด login/register modal แสดงเหตุผลปิดบัญชี และไม่มีเมนูแจ้งเตือน | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route home, tutor.ranking, tutor.search; ใช้ login-modal.blade.php, nav-user-links.blade.php, register-modal.blade.php, tutor-card.blade.php | โค้ดในระบบ |

