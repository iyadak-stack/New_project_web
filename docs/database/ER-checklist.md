# โครงสร้างฐานข้อมูล PeerTutor สำหรับเทียบ ER

## จุดที่กลุ่มควรตกลงก่อนแก้ ER

1. **นักเรียนใน appointments:** ชื่อคอลัมน์ `Student_profiles_student_id` แต่โค้ดรองรับทั้ง `student_profiles.id` และ `users.user_id` ผ่านคนละ relation ปัจจุบันพบ 4 นัดหมายที่ค่าตรงกับ User ID แต่ไม่ตรงกับ StudentProfile ID จึงยังไม่ควรวาดให้เป็น FK บังคับไป student_profiles อย่างเดียว ควรตกลงคีย์ที่ต้องการใช้ก่อนแก้ภายหลัง
2. **ขนาด StudentProfile ID:** migration กำหนด `char(10)` แต่ Model ใช้ HasUuids และ DB จริงมี 1 แถวที่ id ยาว 36 ตัวอักษร SQLite รับได้เพราะไม่บังคับความยาว ต้องพิจารณาถ้าจะใช้ MySQL หรือกำหนดความยาวใน ER
3. **FK จริงกับ relation ต่างกัน:** ตาราง student_profiles, tutor_profiles, favorites, reviews และ pivot ทั้งสองยังไม่ได้ประกาศ FK หลายจุด แม้ Laravel จะเชื่อมข้อมูลได้ รายการ FK จริงอยู่ด้านล่าง
4. **หนึ่งนัดหมายหนึ่งรีวิว:** Model ใช้ hasOne และ Controller กันรีวิวซ้ำ แต่ DB ไม่มี UNIQUE ที่ `reviews.Appointment_Appointment_id` จึงเป็นกฎในโค้ด ไม่ใช่ constraint ใน DB
5. **หนึ่งผู้ใช้หนึ่ง Contacts:** Model ใช้ hasOne แต่ DB ไม่มี UNIQUE ที่ `contacts.Users_user_id` DB จึงยอมให้มีหลายแถวต่อผู้ใช้ได้ ส่วน student_profiles.user_id และ tutor_profiles.Users_user_id มี UNIQUE จริง
6. **PK ของตารางกลาง/หลักฐาน:** Student_profiles_has_Subject ใช้ PK รวมสองคอลัมน์; tutor_profiles_has_subjects ใช้ UNIQUE รวมสองคอลัมน์แต่ไม่มี PK; report_evidences ไม่มี PK หรือ UNIQUE
7. **ผลตรวจล่าสุด:** tutor_profiles มี approval_status/submitted_at/reject_reason; reports มี status และอ้างเหตุผลผ่าน report_reasons ไม่มีตารางประวัติการอนุมัติหรือประวัติการตรวจรายงาน
8. **บทบาท:** นักเรียน ติวเตอร์ และแอดมินใช้ตาราง users ร่วมกัน ไม่ได้แยกตาราง admin; role กับ current_role มีหน้าที่ต่างกัน ผู้สมัครติวเตอร์ที่ยังไม่อนุมัติยังใช้ current_role=student
9. **ชื่อและตัวพิมพ์:** ตารางจริงชื่อ `users` แต่ FK ของ reports อ้างชื่อ `Users`; pivot นักเรียนชื่อ `Student_profiles_has_Subject` ตามจริง SQLite ไม่แยกตัวพิมพ์แบบบางระบบ จึงควรระวังตอนทำ ER และเตรียมย้าย DB
10. **ข้อมูลสรุป:** tutor_profiles เก็บ average_rating และ total_teaching_seconds; จำนวนรีวิวเป็นค่าที่ query นับ ไม่มี reviews_count ในตาราง

ข้อสังเกตเหล่านี้ยังไม่ได้แก้ไข และไม่ได้เสนอให้เพิ่ม FK/PK ทันทีโดยไม่ตรวจข้อมูลเดิม โดยเฉพาะส่วน appointments/availability ที่ผู้ใช้ห้ามแก้

## ความสัมพันธ์สำหรับเทียบ ER

ตารางนี้รวมความสัมพันธ์ที่โค้ดใช้ด้วย จึงต้องดูคอลัมน์ “DB บังคับ FK” ประกอบ ไม่ใช่ ER ของ constraint อย่างเดียว

| ตารางแม่ → ตารางลูก | คอลัมน์เชื่อมในลูก | ความสัมพันธ์ที่ระบบใช้ | DB บังคับ FK |
| --- | --- | --- | --- |
| users → student_profiles | user_id | 1 : 0..1 มี UNIQUE | ไม่มี |
| users → tutor_profiles | Users_user_id | 1 : 0..1 มี UNIQUE | ไม่มี |
| users → contacts | Users_user_id | โค้ด 1 : 0..1 แต่ DB รับหลายแถวได้ | มี |
| users → availabilities | user_id | 1 : N | มี (RESTRICT เมื่อลบ users จริง) |
| users → favorites | Users_user_id | 1 : N | ไม่มี |
| tutor_profiles → favorites | Tutor_profiles_tutor_id (nullable) | 1 : N | ไม่มี |
| subjects → favorites | Subject_subject_id (nullable) | 1 : N | ไม่มี |
| tutor_profiles ↔ subjects | tutor_profiles_has_subjects | N : M มี UNIQUE ของคู่ | ไม่มีทั้งสองฝั่ง |
| student_profiles ↔ subjects | Student_profiles_has_Subject | N : M มี PK รวมของคู่ | ไม่มีทั้งสองฝั่ง |
| subjects → appointments | Subject_subject_id | 1 : N | มี |
| tutor_profiles → appointments | Tutor_profiles_tutor_id | 1 : N | ไม่มี |
| users / student_profiles → appointments | Student_profiles_student_id | 1 : N เป้าหมายยังใช้สองรูปแบบ | ไม่มี |
| appointments → reviews | Appointment_Appointment_id | โค้ด 1 : 0..1 แต่ DB รับหลายแถวได้ | ไม่มี |
| tutor_profiles → reviews | Tutor_profiles_tutor_id | 1 : N | ไม่มี |
| users → reports | Users_user_id | 1 : N | มี อ้างชื่อ Users |
| tutor_profiles → reports | Tutor_profiles_tutor_id (nullable) | 1 : N | ไม่มี |
| report_reasons → reports | ReportReason_Reason_id | 1 : N | มี |
| reports → report_evidences | Report_Report_id | 1 : N | มี |
| users → notifications | Users_user_id | 1 : N | ไม่มี |
| notification_types → notifications | NotificationType_notification_type_id | 1 : N | มี |
| users → passkeys | user_id | 1 : N | มี |
| users → sessions | user_id (nullable) | 1 : N | ไม่มี |

favorites ไม่มี CHECK บังคับว่าต้องอ้างติวเตอร์หรือวิชาอย่างใดอย่างหนึ่ง และไม่มี UNIQUE บังคับคู่ผู้ใช้/ติวเตอร์ จึงควรเทียบกฎที่ต้องการกับ ER ด้วย

## ค่าสถานะที่โค้ดใช้

| ตาราง/คอลัมน์ | ค่า |
| --- | --- |
| users.current_role | student, tutor, admin |
| tutor_profiles.approval_status | draft, pending, approved, rejected |
| tutor_profiles.teaching_mode | online, onsite, both |
| appointments.status | pending, confirmed, cancelled, completed |
| reports.status | pending, in_progress, resolved |

ค่าสถานะเหล่านี้เป็น string และกฎ validation ในโค้ด ไม่ได้เป็น ENUM/CHECK ใน DB ต้องแยกให้ชัดตอนเขียน ER


ตรวจวันที่ 13 ตุลาคม 2026 จาก `database/database.sqlite` แบบ read-only และไฟล์ migration ปัจจุบัน ไม่แก้ DB, migration หรือโค้ดระบบ

พบ **25 ตาราง**: ตารางระบบงาน 16 ตาราง และตารางประกอบ Laravel/ความปลอดภัย 9 ตาราง

Migration มี 27 ไฟล์และถูกรันทั้งหมด ไม่มีรายการ pending หรือไฟล์ migration ที่หายไป

สร้าง schema จาก migration ใน SQLite `:memory:` เพื่อเทียบกับ DB จริงเท่านั้น: **ตรงกันทุกตารางในด้านคอลัมน์ ชนิด SQLite ค่าเริ่มต้น PK/FK และ indexes ที่ตรวจ**

หมายเหตุ: การเทียบ schema ไม่ได้ยืนยันว่าข้อมูลทุกแถวตรงตามกฎธุรกิจ และชื่อคอลัมน์ที่ดูเหมือน FK ไม่ได้แปลว่ามี constraint อยู่จริง

## ตารางหลัก

| ตาราง | ใช้เก็บ | PK จริง |
| --- | --- | --- |
| `users` | บัญชีผู้ใช้ นักเรียน ติวเตอร์ และแอดมิน | `user_id` |
| `student_profiles` | ข้อมูลโปรไฟล์นักเรียน | `id` |
| `tutor_profiles` | โปรไฟล์ติวเตอร์และผลอนุมัติล่าสุด | `tutor_id` |
| `contacts` | ช่องทางติดต่อและสถานที่เรียน | `contact_id` |
| `subjects` | รายวิชากลาง | `subject_id` |
| `Student_profiles_has_Subject` | ตารางกลางวิชาที่นักเรียนสนใจ | `Student_profiles_student_id` + `Subject_subject_id` |
| `tutor_profiles_has_subjects` | ตารางกลางวิชาที่ติวเตอร์สอน | ไม่มี PK |
| `availabilities` | เวลาว่างของบัญชีผู้ใช้ | `availability_id` |
| `appointments` | นัดหมายและรายละเอียดการเรียน | `Appointment_id` |
| `favorites` | รายการโปรดของผู้ใช้ | `favorite_id` |
| `notification_types` | ประเภทแจ้งเตือน | `notification_type_id` |
| `notifications` | แจ้งเตือนของแต่ละบัญชี | `notification_id` |
| `reviews` | รีวิวของนัดหมายที่เรียนจบ | `Review_id` |
| `report_reasons` | เหตุผล/ประเภทการรายงาน | `Reason_id` |
| `reports` | รายงานปัญหาและสถานะล่าสุด | `Report_id` |
| `report_evidences` | ไฟล์หลักฐานของรายงาน | ไม่มี PK |

## ตารางประกอบ

| ตาราง | คอลัมน์ |
| --- | --- |
| `cache` | `key`, `value`, `expiration` |
| `cache_locks` | `key`, `owner`, `expiration` |
| `failed_jobs` | `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at` |
| `job_batches` | `id`, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at` |
| `jobs` | `id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at` |
| `migrations` | `id`, `migration`, `batch` |
| `passkeys` | `id`, `user_id`, `name`, `credential_id`, `credential`, `last_used_at`, `created_at`, `updated_at` |
| `password_reset_tokens` | `email`, `token`, `created_at` |
| `sessions` | `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity` |

## คอลัมน์ทั้งหมด

`Nullable` คือรับ NULL ได้; `PK 1/2` คือลำดับในคีย์รวม; คอลัมน์ migration type อ่านจากไฟล์ที่ประกาศไว้ ส่วน SQLite type เป็นชนิดที่ DB ปัจจุบันรายงาน

SQLite ไม่บังคับความยาว char/varchar แบบ MySQL และ decimal(3,2) รายงานเป็น numeric จึงต้องใช้ชนิด/ความยาวจาก migration ประกอบ ER หากเตรียมย้าย DB

### users

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `email` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(email) |
| `email_verified_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `password` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `first_name` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `last_name` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `role` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `is_active` | boolean | tinyint(1) | ไม่ได้ | '1' | — |
| `profile_picture` | string(255) | varchar | ได้ | ไม่กำหนด | — |
| `remember_token` | string(100) | varchar | ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `deleted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `two_factor_secret` | text | TEXT | ได้ | ไม่กำหนด | — |
| `two_factor_recovery_codes` | text | TEXT | ได้ | ไม่กำหนด | — |
| `two_factor_confirmed_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `current_role` | string(255) | varchar | ไม่ได้ | 'student' | — |

### student_profiles

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(user_id) |
| `bio` | text | TEXT | ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### tutor_profiles

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `tutor_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `Users_user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(Users_user_id) |
| `bio` | text | TEXT | ได้ | ไม่กำหนด | — |
| `experience_years` | integer | INTEGER | ไม่ได้ | '0' | — |
| `total_teaching_seconds` | integer | INTEGER | ไม่ได้ | '0' | — |
| `average_rating` | decimal(3, 2) | numeric | ไม่ได้ | '0' | — |
| `teaching_mode` | string(255) | varchar | ไม่ได้ | 'both' | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `approval_status` | string(20) | varchar | ไม่ได้ | 'draft' | — |
| `submitted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `reject_reason` | text | TEXT | ได้ | ไม่กำหนด | — |

### contacts

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `contact_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `line_id` | string(45) | varchar | ได้ | ไม่กำหนด | — |
| `discord_id` | string(255) | varchar | ได้ | ไม่กำหนด | — |
| `google_meet_link` | string(255) | varchar | ได้ | ไม่กำหนด | — |
| `zoom_link` | string(255) | varchar | ได้ | ไม่กำหนด | — |
| `Users_user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → users.user_id |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `meeting_location` | string(500) | varchar | ได้ | ไม่กำหนด | — |

### subjects

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `subject_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `subject_name` | string(45) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### Student_profiles_has_Subject

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Student_profiles_student_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `Subject_subject_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 2 |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### tutor_profiles_has_subjects

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Tutor_profiles_tutor_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(Tutor_profiles_tutor_id, Subject_subject_id) |
| `Subject_subject_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(Tutor_profiles_tutor_id, Subject_subject_id) |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `deleted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### availabilities

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `availability_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → users.user_id |
| `start_datetime` | dateTime | datetime | ไม่ได้ | ไม่กำหนด | — |
| `end_datetime` | dateTime | datetime | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: user_id

### appointments

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Appointment_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `status` | string(45) | varchar | ไม่ได้ | 'pending' | — |
| `start_datetime` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `end_datetime` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `Subject_subject_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → subjects.subject_id |
| `Tutor_profiles_tutor_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `Student_profiles_student_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `deleted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `meeting_zoom` | string(500) | varchar | ได้ | ไม่กำหนด | — |
| `meeting_meet` | string(500) | varchar | ได้ | ไม่กำหนด | — |
| `meeting_location` | string(500) | varchar | ได้ | ไม่กำหนด | — |
| `lesson_notes` | text | TEXT | ได้ | ไม่กำหนด | — |
| `rejection_reason` | text | TEXT | ได้ | ไม่กำหนด | — |
| `lesson_details_customized` | boolean | tinyint(1) | ไม่ได้ | '0' | — |

### favorites

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `favorite_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `Users_user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `Tutor_profiles_tutor_id` | char(10) | varchar | ได้ | ไม่กำหนด | — |
| `Subject_subject_id` | char(10) | varchar | ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ไม่ได้ | CURRENT_TIMESTAMP | — |
| `deleted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### notification_types

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `notification_type_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `type_name` | string(50) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### notifications

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `notification_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `message` | string(200) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `is_read` | boolean | tinyint(1) | ไม่ได้ | '0' | — |
| `Users_user_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `NotificationType_notification_type_id` | string(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → notification_types.notification_type_id |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### reviews

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Review_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `rating` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `Comment` | text | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `deleted_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `Appointment_Appointment_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `Tutor_profiles_tutor_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | — |

### report_reasons

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Reason_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `reason_name` | string(45) | varchar | ไม่ได้ | ไม่กำหนด | — |

### reports

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `Report_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `description` | text | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `status` | string(45) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ไม่ได้ | CURRENT_TIMESTAMP | — |
| `updated_at` | timestamp | datetime | ไม่ได้ | CURRENT_TIMESTAMP | — |
| `ReportReason_Reason_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → report_reasons.Reason_id |
| `Users_user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → Users.user_id |
| `Tutor_profiles_tutor_id` | char(10) | varchar | ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: Tutor_profiles_tutor_id

### report_evidences

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `FilePath` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `file_type` | string(45) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ไม่ได้ | CURRENT_TIMESTAMP | — |
| `Report_Report_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → reports.Report_id |

### cache

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `key` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `value` | ดู migration / Laravel | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `expiration` | bigInteger | INTEGER | ไม่ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: expiration

### cache_locks

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `key` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `owner` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `expiration` | bigInteger | INTEGER | ไม่ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: expiration

### failed_jobs

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | PK 1 |
| `uuid` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(uuid) |
| `connection` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `queue` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `payload` | longText | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `exception` | longText | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `failed_at` | timestamp | datetime | ไม่ได้ | CURRENT_TIMESTAMP | — |

Index ที่ไม่ใช่ UNIQUE: connection, queue, failed_at

### job_batches

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `name` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `total_jobs` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `pending_jobs` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `failed_jobs` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `failed_job_ids` | longText | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `options` | ดู migration / Laravel | TEXT | ได้ | ไม่กำหนด | — |
| `cancelled_at` | integer | INTEGER | ได้ | ไม่กำหนด | — |
| `created_at` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `finished_at` | integer | INTEGER | ได้ | ไม่กำหนด | — |

### jobs

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | PK 1 |
| `queue` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `payload` | longText | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `attempts` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `reserved_at` | unsignedInteger | INTEGER | ได้ | ไม่กำหนด | — |
| `available_at` | unsignedInteger | INTEGER | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | unsignedInteger | INTEGER | ไม่ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: queue

### migrations

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | PK 1 |
| `migration` | ดู migration / Laravel | varchar | ไม่ได้ | ไม่กำหนด | — |
| `batch` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | — |

### passkeys

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | ดู migration / Laravel | INTEGER | ไม่ได้ | ไม่กำหนด | PK 1 |
| `user_id` | char(10) | varchar | ไม่ได้ | ไม่กำหนด | FK → users.user_id |
| `name` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `credential_id` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | UNIQUE(credential_id) |
| `credential` | ดู migration / Laravel | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `last_used_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |
| `updated_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: user_id

### password_reset_tokens

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `email` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `token` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | — |
| `created_at` | timestamp | datetime | ได้ | ไม่กำหนด | — |

### sessions

| คอลัมน์ | Migration type | SQLite type | Nullable | Default | Key |
| --- | --- | --- | --- | --- | --- |
| `id` | string(255) | varchar | ไม่ได้ | ไม่กำหนด | PK 1 |
| `user_id` | char(10) | varchar | ได้ | ไม่กำหนด | — |
| `ip_address` | string(45) | varchar | ได้ | ไม่กำหนด | — |
| `user_agent` | text | TEXT | ได้ | ไม่กำหนด | — |
| `payload` | longText | TEXT | ไม่ได้ | ไม่กำหนด | — |
| `last_activity` | integer | INTEGER | ไม่ได้ | ไม่กำหนด | — |

Index ที่ไม่ใช่ UNIQUE: last_activity; user_id

## Foreign Key ที่ DB บังคับจริง

| จากคอลัมน์ | อ้างอิง | เมื่อลบแถวแม่ |
| --- | --- | --- |
| `appointments.Subject_subject_id` | `subjects.subject_id` | CASCADE |
| `availabilities.user_id` | `users.user_id` | RESTRICT |
| `contacts.Users_user_id` | `users.user_id` | CASCADE |
| `notifications.NotificationType_notification_type_id` | `notification_types.notification_type_id` | CASCADE |
| `passkeys.user_id` | `users.user_id` | CASCADE |
| `report_evidences.Report_Report_id` | `reports.Report_id` | NO ACTION |
| `reports.Users_user_id` | `Users.user_id` | NO ACTION |
| `reports.ReportReason_Reason_id` | `report_reasons.Reason_id` | NO ACTION |

## รายการ migration ที่รันแล้ว

| Migration | Batch |
| --- | --- |
| `0001_01_01_000000_create_users_table` | 1 |
| `0001_01_01_000001_create_cache_table` | 1 |
| `0001_01_01_000002_create_jobs_table` | 1 |
| `2024_01_01_000000_create_passkeys_table` | 1 |
| `2025_08_14_170933_add_two_factor_columns_to_users_table` | 1 |
| `2026_09_14_140329_create_tutor_profiles_table` | 1 |
| `2026_09_14_140446_create_favorites_table` | 1 |
| `2026_09_16_000000_create_availabilities_table` | 1 |
| `2026_09_18_032630_create_tutor_profiles_has_subject_table` | 1 |
| `2026_09_22_163929_create_student_profiles_table` | 1 |
| `2026_09_22_170954_add_current_role_to_users_table` | 1 |
| `2026_09_25_082910_create_subjects_table` | 1 |
| `2026_09_25_082922_create_notification_types_table` | 1 |
| `2026_09_25_082928_create_appointments_table` | 1 |
| `2026_09_25_082932_create_notifications_table` | 1 |
| `2026_09_29_154823_create_reviews_table` | 1 |
| `2026_09_29_163329_create_reports_table` | 1 |
| `2026_09_29_163335_create_report_reasons_table` | 1 |
| `2026_09_29_163340_create_report_evidence_table` | 1 |
| `2026_09_29_163355_bridge_booking_schema` | 1 |
| `2026_10_09_194915_create_contacts_table` | 1 |
| `2026_10_10_170307_create_student_profiles_has_subject_table` | 1 |
| `2026_10_13_000000_add_reported_tutor_to_reports_table` | 1 |
| `2026_10_13_010000_add_tutor_approval_columns` | 2 |
| `2026_10_11_000002_add_tutor_approval_and_lesson_details` | 3 |
| `2026_10_11_000003_add_lesson_override_flag` | 4 |
| `2026_10_13_020000_add_user_foreign_key_to_availabilities_table` | 5 |
