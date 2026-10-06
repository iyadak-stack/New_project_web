<?php

namespace App\Domains\Auth\Actions\Fortify;

use App\Domains\Auth\Concerns\PasswordValidationRules;
use App\Domains\Auth\Concerns\ProfileValidationRules;
use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $nameParts = preg_split('/\s+/u', trim($input['name']), 2) ?: [];

        return User::create([
            'user_id' => strtoupper(Str::random(10)),
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => 'student',
            'current_role' => 'student',
            'is_active' => true,
        ]);
    }
}
