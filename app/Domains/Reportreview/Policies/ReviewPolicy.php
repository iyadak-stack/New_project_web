<?php

namespace App\Domains\Reportreview\Policies;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Review;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->current_role === 'student' && $user->studentProfile()->exists();
    }

    public function update(User $user, Review $review): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $review->appointment?->studentProfile?->user_id === $user->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $review->appointment?->studentProfile?->user_id === $user->user_id;
    }
}
