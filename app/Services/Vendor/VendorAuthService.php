<?php

namespace App\Services\Vendor;

use App\Mail\VendorPasswordResetMail;
use App\Models\AppUser;
use App\Models\Vendor;
use App\Services\Auth\AppAuthService;
use App\Services\Auth\AppleTokenVerifier;
use App\Services\Auth\GoogleTokenVerifier;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorAuthService
{
    public function __construct(
        protected AppAuthService $authService,
        protected GoogleTokenVerifier $googleTokenVerifier,
        protected AppleTokenVerifier $appleTokenVerifier,
        protected SettingsService $settingsService
    ) {
    }

    public function register(array $data): array
    {
        $vendorSettings = $this->settingsService->get('vendor', []);
        $docSettings = $this->settingsService->get('document_verification_settings', []);
        $autoApprove = (bool) ($vendorSettings['auto_approve_vendor'] ?? true);
        $requireDocs = (bool) ($docSettings['isStoreVerification'] ?? false);

        $payload = [
            'reviewsCount' => 0,
            'reviewsSum' => 0,
            'provider' => $data['provider'] ?? 'email',
            'appIdentifier' => $data['appIdentifier'] ?? null,
        ];

        if (! empty($data['provider_uid'])) {
            $payload['provider_uid'] = $data['provider_uid'];
        }

        $user = AppUser::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'firstName' => $data['firstName'] ?? $data['first_name'],
            'lastName' => $data['lastName'] ?? $data['last_name'] ?? null,
            'email' => $data['email'],
            'phoneNumber' => $data['phoneNumber'] ?? $data['phone'] ?? null,
            'countryCode' => $data['countryCode'] ?? null,
            'password' => Hash::make($data['password'] ?? Str::random(16)),
            'role' => 'vendor',
            'active' => $autoApprove,
            'isActive' => $autoApprove,
            'isDocumentVerify' => ! $requireDocs,
            'sectionId' => $data['sectionId'] ?? $data['section_id'] ?? null,
            'section_id' => $data['section_id'] ?? $data['sectionId'] ?? null,
            'zoneId' => $data['zoneId'] ?? null,
            'fcmToken' => $data['fcmToken'] ?? null,
            'profilePictureURL' => $data['profilePictureURL'] ?? null,
            'wallet_amount' => 0,
            'createdAt' => now(),
            'payload' => array_filter($payload, static fn ($v) => $v !== null),
        ]);

        if (! $autoApprove) {
            return ['user' => $user->fresh(), 'token' => null, 'pending_approval' => true];
        }

        $token = $user->createToken('vendor-api', ['vendor'])->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token, 'pending_approval' => false];
    }

    public function login(string $email, string $password, ?string $fcmToken = null): array
    {
        $user = AppUser::query()->where('email', $email)->where('role', 'vendor')->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        return $this->issueTokenForUser($user, $fcmToken);
    }

    public function loginWithGoogle(string $idToken, array $profile = []): array
    {
        return $this->loginWithSocial('google', $this->googleTokenVerifier->verify($idToken), $profile);
    }

    public function loginWithApple(string $idToken, array $profile = []): array
    {
        return $this->loginWithSocial('apple', $this->appleTokenVerifier->verify($idToken), $profile);
    }

    public function loginWithPhone(array $data): array
    {
        $phone = $data['phoneNumber'] ?? $data['phone'] ?? null;
        $countryCode = $data['countryCode'] ?? null;

        if (! $phone) {
            throw ValidationException::withMessages(['phoneNumber' => ['Phone number is required.']]);
        }

        $query = AppUser::query()->where('role', 'vendor')->where('phoneNumber', $phone);
        if ($countryCode) {
            $query->where('countryCode', $countryCode);
        }

        $user = $query->first();

        if (! $user && ! ($data['auto_register'] ?? true)) {
            return [
                'is_new_user' => true,
                'user' => null,
                'token' => null,
                'profile' => compact('phone', 'countryCode'),
            ];
        }

        if (! $user) {
            $password = $data['password'] ?? Str::random(16);
            $result = $this->register(array_merge($data, [
                'firstName' => $data['firstName'] ?? 'Vendor',
                'email' => $data['email'] ?? ($phone . '@phone.vendor.local'),
                'phoneNumber' => $phone,
                'countryCode' => $countryCode,
                'password' => $password,
                'password_confirmation' => $password,
                'provider' => 'mobileNumber',
            ]));

            return array_merge($result, ['is_new_user' => true]);
        }

        return array_merge($this->issueTokenForUser($user, $data['fcmToken'] ?? null), ['is_new_user' => false]);
    }

    public function forgotPassword(string $email): void
    {
        $user = AppUser::query()->where('email', $email)->where('role', 'vendor')->first();
        if (! $user) {
            return;
        }

        $plainToken = Str::random(64);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($plainToken), 'created_at' => now()]
        );
        Mail::to($email)->send(new VendorPasswordResetMail($user, $plainToken));
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $record = DB::table('password_resets')->where('email', $email)->first();
        if (! $record || ! Hash::check($token, $record->token)) {
            throw ValidationException::withMessages(['token' => ['Invalid or expired password reset token.']]);
        }

        $expiresMinutes = (int) config('auth.passwords.app_users.expire', 60);
        if (now()->diffInMinutes($record->created_at) > $expiresMinutes) {
            DB::table('password_resets')->where('email', $email)->delete();
            throw ValidationException::withMessages(['token' => ['Password reset token has expired.']]);
        }

        $user = AppUser::query()->where('email', $email)->where('role', 'vendor')->first();
        if (! $user) {
            throw ValidationException::withMessages(['email' => ['No vendor account found for this email.']]);
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
        if ($user->vendorID) {
            Vendor::query()->where('id', $user->vendorID)->delete();
        }

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
            throw ValidationException::withMessages(['id_token' => ['Social provider user id is missing from token.']]);
        }

        $user = $this->findSocialUser($provider, $providerUid, $claims['email'] ?? null);

        if (! $user && ! ($profile['auto_register'] ?? true)) {
            return [
                'is_new_user' => true,
                'user' => null,
                'token' => null,
                'profile' => [
                    'email' => $claims['email'] ?? $profile['email'] ?? null,
                    'firstName' => $profile['firstName'] ?? null,
                    'lastName' => $profile['lastName'] ?? null,
                    'provider' => $provider,
                    'provider_uid' => $providerUid,
                ],
            ];
        }

        if (! $user) {
            $email = $claims['email'] ?? $profile['email'] ?? null;
            if (! $email) {
                throw ValidationException::withMessages(['email' => ['Email is required to complete social registration.']]);
            }

            $password = Str::random(16);
            $result = $this->register(array_merge($profile, [
                'firstName' => $profile['firstName'] ?? 'Vendor',
                'lastName' => $profile['lastName'] ?? null,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'provider' => $provider,
                'provider_uid' => $providerUid,
                'profilePictureURL' => $claims['picture'] ?? $profile['profilePictureURL'] ?? null,
            ]));

            return array_merge($result, ['is_new_user' => true]);
        }

        if (! empty($profile['fcmToken'])) {
            $user->update(['fcmToken' => $profile['fcmToken']]);
        }

        $user->mergePayload(['provider' => $provider, 'provider_uid' => $providerUid]);
        $user->save();

        return array_merge($this->issueTokenForUser($user->fresh(), $profile['fcmToken'] ?? null), ['is_new_user' => false]);
    }

    protected function findSocialUser(string $provider, string $providerUid, ?string $email): ?AppUser
    {
        $user = AppUser::query()
            ->where('role', 'vendor')
            ->where('payload->provider', $provider)
            ->where('payload->provider_uid', $providerUid)
            ->first();

        if ($user || ! $email) {
            return $user;
        }

        return AppUser::query()->where('role', 'vendor')->where('email', $email)->first();
    }

    protected function issueTokenForUser(AppUser $user, ?string $fcmToken = null): array
    {
        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['This vendor account is inactive or pending approval.'],
            ]);
        }

        if ($fcmToken) {
            $user->update(['fcmToken' => $fcmToken]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('vendor-api', ['vendor'])->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token, 'is_new_user' => false, 'pending_approval' => false];
    }
}
