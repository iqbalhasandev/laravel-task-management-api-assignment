<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Requests\Api\V1\Auth\LoginRequest;
use App\Requests\Api\V1\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->success([
            ...$user->toArray(),
            'token' => $token,
        ], 'User registered successfully');
    }

    /**
     * Log in an existing user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->error(
                [],
                'Invalid login credentials',
                401
            );
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->success(
            [
                ...$user->toArray(),
                'token' => $token,
            ],
            'Logged in successfully'
        );
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->success(
            [],
            'Logged out successfully',
        );
    }
}
