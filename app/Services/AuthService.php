<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function attemptLogin(array $credentials): ?User
    {
        if (auth()->attempt($credentials)) {
            return auth()->user();
        }
        return null;
    }

    public function logout(User $user): void
    {
        // revoke tokens or perform other cleanup
        $user->tokens()->delete();
    }
}
