<?php

namespace App\Services\Provider;

use App\Mail\ProviderPasswordResetMail;
use App\Models\AppUser;
use App\Services\Auth\AppAuthService;
use App\Services\Auth\AppleTokenVerifier;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProviderAuthService
{
    public function __construct(
        protected AppAuthService $authService,
        protected AppleTokenVerifier $appleTokenVerifier,
        protected SettingsService $settingsService
    ) {
    }

    public function register(array $data): array
    {
        $autoApprove = (bool) data_get($this->settingsService->get('provider', []), 'auto_approve_provider', true);

        $payload = [
            'reviewsCount' => 0,
            'reviewsSum' => 0,
            'street' => $data['street'] ?? null,
            'area' => $data['area'] ?? null,
        ];

        if (! empty($data['adminCommission'])) {
            $payload['adminCommission'] = $data['adminCommission'];
        }

        if (! empty($data['provider'])) {
            $payload['provider'] = $data['provider'];
            $payload['provider_uid'] = $data['provider_uid'] ?? null;
        }

        $user = AppUser::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'firstName' => $data['firstName'] ?? $data['first_name'],
            'lastName' => $data['lastName'] ?? $data['last_name'] ?? null,
            'email' => $data['email'],
            'phoneNumber' => $data['phoneNumber'] ?? $data['phone'] ?? null,
            'countryCode' => $data['countryCode'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'provider',
            'active' => $autoApprove,
            'isActive' => $autoApprove,
            'sectionId' => $data['sectionId'] ?? $data['section_id'] ?? null,
            'section_id' => $data['section_id'] ?? $data['sectionId'] ?? null,
            'fcmToken' => $data['fcmToken'] ?? null,
            'latitude' => $data['latitude'] ?? data_get($data, 'location.latitude'),
            'longitude' => $data['longitude'] ?? data_get($data, 'location.longitude'),
            'profilePictureURL' => $data['profilePictureURL'] ?? null,
            'wallet_amount' => 0,
            'createdAt' => now(),
            'payload' => array_filter($payload, static fn ($v) => $v !== null),
        ]);

        if (! $autoApprove) {
            return [
                'user' => $user->fresh(),
                'token' => null,
                'pending_approval' => true,
            ];
        }

        $token = $user->createToken('provider-api', ['provider'])->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
            'pending_approval' => false,
        ];
    }

    public function login(string $email, string $password, ?string $fcmToken = null): array
    {
        $user = AppUser::query()
            ->where('email', $email)
            ->where('role', 'provider')
            ->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->issueTokenForUser($user, $fcmToken);
    }

    public function loginWithApple(string $idToken, array $profile = []): array
    {
        $claims = $this->appleTokenVerifier->verify($idToken);

        return $this->loginWithSocial('apple', $claims, $profile);
    }

    public function loginWithPhone(array $data): array
    {
        $phone = $data['phoneNumber'] ?? $data['phone'] ?? null;
        $countryCode = $data['countryCode'] ?? null;

        if (! $phone) {
            throw ValidationException::withMessages([
                'phoneNumber' => ['Phone number is required.'],
            ]);
        }

        $query = AppUser::query()->where('role', 'provider')->where('phoneNumber', $phone);
        if ($countryCode) {
            $query->where('countryCode', $countryCode);
        }

        $user = $query->first();

        if (! $user) {
            if (! ($data['auto_register'] ?? true)) {
                return [
                    'is_new_user' => true,
                    'user' => null,
                    'token' => null,
                    'profile' => [
                        'phoneNumber' => $phone,
                        'countryCode' => $countryCode,
                    ],
                ];
            }

            $password = $data['password'] ?? Str::random(16);

            $result = $this->register([
                'firstName' => $data['firstName'] ?? 'Provider',
                'lastName' => $data['lastName'] ?? null,
                'email' => $data['email'] ?? ($phone . '@phone.provider.local'),
                'phoneNumber' => $phone,
                'countryCode' => $countryCode,
                'password' => $password,
                'password_confirmation' => $password,
                'sectionId' => $data['sectionId'] ?? null,
                'fcmToken' => $data['fcmToken'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);

            return array_merge($result, ['is_new_user' => true]);
        }

        return array_merge(
            $this->issueTokenForUser($user, $data['fcmToken'] ?? null),
            ['is_new_user' => false]
        );
    }

    public function forgotPassword(string $email): void
    {
        $user = AppUser::query()
            ->where('email', $email)
            ->where('role', 'provider')
            ->first();

        if (! $user) {
            return;
        }

        $plainToken = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        Mail::to($email)->send(new ProviderPasswordResetMail($user, $plainToken));
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (! $record || ! Hash::check($token, $record->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired password reset token.'],
            ]);
        }

        $expiresMinutes = (int) config('auth.passwords.app_users.expire', 60);

        if (now()->diffInMinutes($record->created_at) > $expiresMinutes) {
            DB::table('password_resets')->where('email', $email)->delete();

            throw ValidationException::withMessages([
                'token' => ['Password reset token has expired.'],
            ]);
        }

        $user = AppUser::query()
            ->where('email', $email)
            ->where('role', 'provider')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No provider account found for this email.'],
            ]);
        }

        $user->update(['password' => Hash::make($password)]);
        $user->tokens()->delete();
        DB::table('password_resets')->where('email', $email)->delete();
    }

    public function logout(AppUser $user): void
    {
        $this->authService->logout($user);
    }

    public function deleteAccount(AppUser $user): void
    {
        $user->tokens()->delete();
        $user->update([
            'active' => false,
            'isActive' => false,
            'fcmToken' => null,
            'email' => 'deleted_' . $user->id . '_' . ($user->email ?? ''),
        ]);
        $user->mergePayload(['deleted_at' => now()->toIso8601String()]);
        $user->save();
    }

    protected function loginWithSocial(string $provider, array $claims, array $profile): array
    {
        $providerUid = $claims['sub'] ?? null;

        if (! $providerUid) {
            throw ValidationException::withMessages([
                'id_token' => ['Social provider user id is missing from token.'],
            ]);
        }

        $user = $this->findSocialUser($provider, $providerUid, $claims['email'] ?? null);

        if (! $user && ! ($profile['auto_register'] ?? true)) {
            return [
                'is_new_user' => true,
                'user' => null,
                'token' => null,
                'profile' => [
                    'email' => $claims['email'] ?? $profile['email'] ?? null,
                    'firstName' => $profile['firstName'] ?? $this->firstNameFromName($claims['name'] ?? null),
                    'lastName' => $profile['lastName'] ?? $this->lastNameFromName($claims['name'] ?? null),
                    'provider' => $provider,
                    'provider_uid' => $providerUid,
                ],
            ];
        }

        if (! $user) {
            $email = $claims['email'] ?? $profile['email'] ?? null;
            if (! $email) {
                throw ValidationException::withMessages([
                    'email' => ['Email is required to complete social registration.'],
                ]);
            }

            $password = Str::random(16);

            $result = $this->register([
                'firstName' => $profile['firstName'] ?? $this->firstNameFromName($claims['name'] ?? null) ?? 'Provider',
                'lastName' => $profile['lastName'] ?? $this->lastNameFromName($claims['name'] ?? null),
                'email' => $email,
                'phoneNumber' => $profile['phoneNumber'] ?? null,
                'password' => $password,
                'password_confirmation' => $password,
                'fcmToken' => $profile['fcmToken'] ?? null,
                'profilePictureURL' => $claims['picture'] ?? $profile['profilePictureURL'] ?? null,
                'provider' => $provider,
                'provider_uid' => $providerUid,
                'sectionId' => $profile['sectionId'] ?? null,
            ]);

            return array_merge($result, ['is_new_user' => true]);
        }

        if (! empty($profile['fcmToken'])) {
            $user->update(['fcmToken' => $profile['fcmToken']]);
        }

        $user->mergePayload([
            'provider' => $provider,
            'provider_uid' => $providerUid,
        ]);
        $user->save();

        return array_merge(
            $this->issueTokenForUser($user->fresh(), $profile['fcmToken'] ?? null),
            ['is_new_user' => false]
        );
    }

    protected function findSocialUser(string $provider, string $providerUid, ?string $email): ?AppUser
    {
        $user = AppUser::query()
            ->where('role', 'provider')
            ->where('payload->provider', $provider)
            ->where('payload->provider_uid', $providerUid)
            ->first();

        if ($user) {
            return $user;
        }

        if ($email) {
            return AppUser::query()
                ->where('role', 'provider')
                ->where('email', $email)
                ->first();
        }

        return null;
    }

    protected function issueTokenForUser(AppUser $user, ?string $fcmToken = null): array
    {
        if (! $user->active || ! $user->isActive) {
            throw ValidationException::withMessages([
                'email' => ['This provider account is inactive or pending approval.'],
            ]);
        }

        if ($fcmToken) {
            $user->update(['fcmToken' => $fcmToken]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('provider-api', ['provider'])->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
            'is_new_user' => false,
            'pending_approval' => false,
        ];
    }

    protected function firstNameFromName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return explode(' ', trim($name), 2)[0] ?? null;
    }

    protected function lastNameFromName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $parts = explode(' ', trim($name), 2);

        return $parts[1] ?? null;
    }
}
