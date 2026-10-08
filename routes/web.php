<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\Domains\TutorProfile\Http\Controllers\TutorController;
use App\Domains\TutorProfile\Http\Controllers\FavoriteController;
use App\Domains\Booking\Http\Controllers\SubjectController;
use App\Domains\Booking\Http\Controllers\AppointmentController;
use App\Domains\Scheduling\Http\Controllers\AvailabilityController;
use App\Domains\Scheduling\Http\Controllers\CheckScheduleController;
use App\Domains\Booking\Http\Controllers\NotificationController;
use App\Domains\Auth\Http\Controllers\ContactController;
use App\Domains\TutorProfile\Http\Controllers\StudentProfileController;
use App\Domains\Auth\Http\Controllers\RoleController;
use App\Domains\Reportreview\Http\Controllers\ReviewController;
use App\Domains\Reportreview\Http\Controllers\Admin\ReportController;
use App\Domains\Auth\Models\User;

// หน้าแรก: ตรวจสอบบทบาท ล็อกอินเป็น tutor ให้ไปหน้า tutor.profile ทันที
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        if (auth()->user()->current_role === 'tutor') {
            return redirect()->route('tutor.profile');
        }
    }
    return app(TutorController::class)->home($request);
})->name('home');

// =========================
// ลืมรหัสผ่านแบบข้ามการส่งอีเมล (Direct Reset Password)
// =========================

Route::post('/forgot-password', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ], [
        'email.exists' => 'ไม่พบอีเมลนี้ในระบบ',
    ]);

    // เด้งไปหน้ากรอกรหัสผ่านใหม่ (Step 2) ทันที
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


Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

    // =========================
    // โปรไฟล์ติวเตอร์ (Tutor Profile)
    // =========================

    Route::get('/tutor/profile', [TutorController::class, 'profile'])
        ->middleware('role:tutor')
        ->name('tutor.profile');

    Route::get('/tutor/profile/edit', [TutorController::class, 'editProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.edit');

    Route::post('/tutor/profile', [TutorController::class, 'updateProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.update');


    // =========================
    // รายการโปรดติวเตอร์ (Tutor Favorite)
    // =========================

    Route::post('/tutor/{tutorProfile}/favorite', [FavoriteController::class, 'storeTutor'])
        ->name('tutor.favorite.store');

    Route::post('/tutor/{tutorProfile}/favorite/remove', [FavoriteController::class, 'destroyTutor'])
        ->name('tutor.favorite.destroy');


    // =========================
    // ค้นหา / จัดอันดับติวเตอร์ (Search / Ranking / Tutor)
    // =========================

    Route::get('/tutors', [TutorController::class, 'search'])
        ->name('tutor.search');

    Route::get('/tutors/ranking', [TutorController::class, 'ranking'])
        ->name('tutor.ranking');

    Route::get('/tutors/favorites', [FavoriteController::class, 'tutorFavorites'])
        ->name('tutor.favorites');

    Route::get('/tutors/{tutorProfile}', [TutorController::class, 'show'])
        ->name('tutor.show');


    // =========================
    // จัดการรายวิชา (Subjects)
    // =========================

    Route::resource('subjects', SubjectController::class);


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

    Route::post('/student/profile', [StudentProfileController::class, 'updateProfile'])
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