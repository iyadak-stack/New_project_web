<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\Domains\TutorProfile\Http\Controllers\StudentHomeController;
use App\Domains\TutorProfile\Http\Controllers\StudentProfileController;
use App\Domains\TutorProfile\Http\Controllers\TutorProfileController;
use App\Domains\TutorProfile\Http\Controllers\TutorSearchController;

use App\Domains\TutorProfile\Http\Controllers\FavoriteController;
use App\Domains\Booking\Http\Controllers\SubjectController;
use App\Domains\Booking\Http\Controllers\AppointmentController;
use App\Domains\Scheduling\Http\Controllers\AvailabilityController;
use App\Domains\Scheduling\Http\Controllers\CheckScheduleController;
use App\Domains\Booking\Http\Controllers\NotificationController;
use App\Domains\Auth\Http\Controllers\ContactController;
use App\Domains\Auth\Http\Controllers\RoleController;
use App\Domains\Reportreview\Http\Controllers\ReviewController;
use App\Domains\Reportreview\Http\Controllers\Admin\ReportController;
use App\Domains\Auth\Models\User;

// =========================
// หน้าแรก (/): ตรวจสอบบทบาทผู้ใช้
// =========================
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        if (auth()->user()->current_role === 'tutor') {
            return view('domains.tutor-profile.tutor.tutor');
        }

        return app(StudentHomeController::class)->index($request);
    }

    return app(StudentHomeController::class)->index($request);
})->name('home');


// =========================
// ลืมรหัสผ่านแบบข้ามการส่งอีเมล (Direct Reset Password)
// หมายเหตุ: ใช้เฉพาะตอนพัฒนา ห้ามใช้บนระบบจริง (ไม่มีการตรวจ token)
// =========================
Route::post('/forgot-password', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ], [
        'email.exists' => 'ไม่พบอีเมลนี้ในระบบ',
    ]);

    return redirect()->route('password.reset', [
        'token' => 'direct-reset',
        'email' => $request->email,
    ]);
})->name('password.email');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::where('email', $request->email)->first();
    if ($user) {
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();
    }

    return redirect()->route('login')->with('status', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว กรุณาเข้าสู่ระบบ');
})->name('password.update');


// =========================
// ค้นหา / ดูโปรไฟล์ติวเตอร์ (Public Views Access)
// สำคัญ: route ที่เป็นคำตายตัว (ranking, favorites) ต้องอยู่ก่อน /tutors/{id}
// =========================
Route::get('/tutors', [TutorSearchController::class, 'search'])
    ->name('tutor.search');

Route::get('/tutors/ranking', [TutorSearchController::class, 'ranking'])
    ->name('tutor.ranking');

// รายการโปรดของฉัน (ต้องล็อกอิน) — ย้ายมาไว้ก่อน /tutors/{id} เพื่อไม่ให้ถูกบัง
Route::get('/tutors/favorites', [FavoriteController::class, 'tutorFavorites'])
    ->middleware('auth')
    ->name('tutor.favorites');

// หน้าโปรไฟล์ติวเตอร์รายบุคคล
Route::get('/tutors/{id}', [TutorProfileController::class, 'show'])
    ->name('tutor.show');


// =========================
// กลุ่ม Route ที่ต้องล็อกอิน (Auth Middleware)
// =========================
Route::middleware(['auth'])->group(function () {

    // กันเข้า URL /dashboard ให้วิ่งกลับไปหน้าหลัก
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');


    // =========================
    // หน้ารวมวิชาทั้งหมดโดยเฉพาะ (Dedicated All Subjects Page)
    // =========================
    Route::get('/subjects', [StudentHomeController::class, 'allSubjects'])
        ->name('subjects.index');

    // หน้าติวเตอร์ทั้งหมดสำหรับนักเรียน
    Route::get('/student/tutors', [StudentHomeController::class, 'allTutors'])
        ->name('student.tutors.index');


    // =========================
    // โปรไฟล์ติวเตอร์ (Tutor Profile - Private/Management)
    // =========================
    Route::get('/tutor/profile', [TutorProfileController::class, 'profile'])
        ->middleware('role:tutor')
        ->name('tutor.profile');

    Route::get('/tutor/profile/edit', [TutorProfileController::class, 'editProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.edit');

    Route::post('/tutor/profile', [TutorProfileController::class, 'updateProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.update');

    Route::post('/tutor/application/submit', [TutorProfileController::class, 'submitApplication'])
        ->middleware('role:tutor')
        ->name('tutor.application.submit');


    // =========================
    // รายการโปรดติวเตอร์ (Tutor Favorite) — POST เท่านั้น
    // =========================
    Route::post('/tutor/{id}/favorite', [FavoriteController::class, 'storeTutor'])
        ->name('tutor.favorite.store');

    Route::post('/tutor/{id}/favorite/remove', [FavoriteController::class, 'destroyTutor'])
        ->name('tutor.favorite.destroy');


    // =========================
    // จัดการรายวิชา (Subjects Resource)
    // =========================
    Route::resource('subjects', SubjectController::class)->except(['index']);


    // =========================
    // ตารางเวลาและความพร้อม (Availability & Schedule Check)
    // =========================
    Route::get('/availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('/availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::get('/availabilities/history', [AvailabilityController::class, 'history'])->name('availabilities.history');
    Route::get('/availabilities/{availability}/edit', [AvailabilityController::class, 'edit'])->name('availabilities.edit');
    Route::put('/availabilities/{availability}', [AvailabilityController::class, 'update'])->name('availabilities.update');
    Route::delete('/availabilities/{availability}', [AvailabilityController::class, 'destroy'])->name('availabilities.destroy');

    Route::get('/schedule/check', [CheckScheduleController::class, 'index'])->name('schedule.check');
    Route::get('/schedule/history', [CheckScheduleController::class, 'history'])->name('schedule.history');
    Route::get('/schedule/booking', [CheckScheduleController::class, 'booking'])->name('schedule.booking');
    Route::post('/schedule/check', [CheckScheduleController::class, 'check'])->name('schedule.check.results');


    // =========================
    // การนัดหมาย (Appointments)
    // =========================
    Route::resource('appointments', AppointmentController::class)
        ->except(['edit', 'update']);

    Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
        ->name('appointments.confirm');

    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])
        ->name('appointments.reschedule');

    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');


    // =========================
    // ระบบแจ้งเตือน (Notifications)
    // =========================
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');


    // =========================
    // โปรไฟล์นักเรียน (Student Profile)
    // =========================
    Route::get('/student/profile', [StudentProfileController::class, 'profile'])
        ->middleware('role:student')
        ->name('student.profile');

    Route::get('/student/profile/edit', [StudentProfileController::class, 'editProfile'])
        ->middleware('role:student')
        ->name('student.profile.edit');

    Route::post('/student/profile/update', [StudentProfileController::class, 'updateProfile'])
        ->middleware('role:student')
        ->name('student.profile.update');


    // =========================
    // ข้อมูลติดต่อของนักเรียน (Student Contacts)
    // =========================
    Route::get('/student-contacts/{studentId}/edit', [ContactController::class, 'edit'])
        ->name('student-contacts.edit');

    Route::put('/student-contacts/{studentId}', [ContactController::class, 'update'])
        ->name('student-contacts.update');


    // =========================
    // สลับบทบาทผู้ใช้ (Role Switching)
    // =========================
    Route::post('/switch-role', [RoleController::class, 'switchRole'])
        ->name('role.switch');


    // =========================
    // รีวิว (Reviews)
    // =========================
    Route::get('/reviews', [ReviewController::class, 'index'])
        ->name('reviews.index');

    Route::get('/reviews/create', [ReviewController::class, 'create'])
        ->name('reviews.create');

    Route::post('/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');

    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');


    // =========================
    // รายงาน (Reports)
    // =========================
    Route::get('/reports/create', [ReportController::class, 'create'])
        ->name('reports.create');

    Route::post('/reports', [ReportController::class, 'store'])
        ->name('reports.store');
});

require __DIR__.'/settings.php';