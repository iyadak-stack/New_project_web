<?php

namespace App\Domains\Reportreview\Policies;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Review;

class ReviewPolicy
{
    public function update(User $user, Review $review): bool
    {
        if ($user->current_role === 'admin') {
            return true;
        }

        return $review->appointment?->studentProfile?->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->current_role === 'admin') {
            return true;
        }

        return $review->appointment?->studentProfile?->user_id === $user->id;
    }
}