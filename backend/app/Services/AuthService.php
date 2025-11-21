<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and log them in.
     */
    public function register(array $data): UserResource
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);

        return new UserResource($user);
    }

    /**
     * Attempt login and return token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): UserResource
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_credentials')],
            ]);
        }

        $user = Auth::user();

        return new UserResource($user);
    }

    /**
     * Logout user by revoking current access token.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    /**
     * Return authenticated user.
     *
     * @throws AuthenticationException
     */
    public function me(?User $user): UserResource
    {
        if (! $user) {
            throw new AuthenticationException();
        }

        return new UserResource($user);
    }
}
