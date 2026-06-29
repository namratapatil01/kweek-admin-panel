<?php

namespace App\Services\Auth;

use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AppAuthService
{
    public function register(array $data): array
    {
        $user = AppUser::query()->create([
            'id' => $data['id'] ?? (string) \Illuminate\Support\Str::uuid(),
            'firstName' => $data['firstName'] ?? $data['first_name'] ?? null,
            'lastName' => $data['lastName'] ?? $data['last_name'] ?? null,
            'email' => $data['email'],
            'phoneNumber' => $data['phoneNumber'] ?? $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'customer',
            'active' => true,
            'isActive' => true,
            'sectionId' => $data['sectionId'] ?? $data['section_id'] ?? null,
            'section_id' => $data['section_id'] ?? $data['sectionId'] ?? null,
            'fcmToken' => $data['fcmToken'] ?? null,
        ]);

        $token = $user->createToken('mobile-api', [$user->role ?? 'customer'])->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password, ?string $fcmToken = null): array
    {
        $user = AppUser::query()->where('email', $email)->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->active || ! $user->isActive) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive.'],
            ]);
        }

        if ($fcmToken) {
            $user->update(['fcmToken' => $fcmToken]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile-api', [$user->role ?? 'customer'])->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(AppUser $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function refresh(AppUser $user): string
    {
        $user->currentAccessToken()?->delete();

        return $user->createToken('mobile-api', [$user->role ?? 'customer'])->plainTextToken;
    }
}
