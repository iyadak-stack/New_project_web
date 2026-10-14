<?php

namespace App\Domains\Reportreview\Policies;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Review;

class ReviewPolicy
{
    public function update(User $user, Review $review): bool
    {
        if ($user->role === 'admin' || $user->current_role === 'admin') {
            return true;
        }

        $appointment = $review->appointment;
        if (! $appointment) {
            return false;
        }

        // นัดหมายเดิมเก็บรหัสโปรไฟล์ ส่วนของเพื่อนเก็บรหัสผู้ใช้
        return (string) $appointment->Student_profiles_student_id === (string) $user->user_id
            || (string) $appointment->studentProfile?->user_id === (string) $user->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $this->update($user, $review);
    }
}
