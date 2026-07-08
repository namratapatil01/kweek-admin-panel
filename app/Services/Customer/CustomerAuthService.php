<?php

namespace App\Services\Customer;

use App\Mail\CustomerPasswordResetMail;
use App\Models\AppUser;
use App\Services\Auth\AppAuthService;
use App\Services\Auth\AppleTokenVerifier;
use App\Services\Auth\GoogleTokenVerifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthService
{
    public function __construct(
        protected AppAuthService $authService,
        protected GoogleTokenVerifier $googleTokenVerifier,
        protected AppleTokenVerifier $appleTokenVerifier
    ) {
    }

    public function register(array $data): array
    {
        $user = AppUser::query()->create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'firstName' => $data['firstName'] ?? $data['first_name'],
            'lastName' => $data['lastName'] ?? $data['last_name'] ?? null,
            'email' => $data['email'],
            'phoneNumber' => $data['phoneNumber'] ?? $data['phone'] ?? null,
            'countryCode' => $data['countryCode'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'active' => true,
            'isActive' => true,
            'sectionId' => $data['sectionId'] ?? $data['section_id'] ?? null,
            'section_id' => $data['section_id'] ?? $data['sectionId'] ?? null,
            'fcmToken' => $data['fcmToken'] ?? null,
            'wallet_amount' => 0,
            'createdAt' => now(),
        ]);

        if (! empty($data['provider'])) {
            $user->mergePayload([
                'provider' => $data['provider'],
                'provider_uid' => $data['provider_uid'] ?? null,
            ]);
            $user->save();
        }

        $token = $user->createToken('customer-api', ['customer'])->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token];
    }

    public function login(string $email, string $password, ?string $fcmToken = null): array
    {
        $user = AppUser::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->issueTokenForUser($user, $fcmToken);
    }

    public function loginWithGoogle(string $idToken, array $profile = []): array
    {
        $claims = $this->googleTokenVerifier->verify($idToken);

        return $this->loginWithSocial('google', $claims, $profile);
    }

    public function loginWithApple(string $idToken, array $profile = []): array
    {
        $claims = $this->appleTokenVerifier->verify($idToken);

        return $this->loginWithSocial('apple', $claims, $profile);
    }

    public function forgotPassword(string $email): void
    {
        $user = AppUser::query()
            ->where('email', $email)
            ->where('role', 'customer')
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

        Mail::to($email)->send(new CustomerPasswordResetMail($user, $plainToken));
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
            ->where('role', 'customer')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No customer account found for this email.'],
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
                    'firstName' => $profile['firstName'] ?? $claims['given_name'] ?? $this->firstNameFromName($claims['name'] ?? null),
                    'lastName' => $profile['lastName'] ?? $claims['family_name'] ?? $this->lastNameFromName($claims['name'] ?? null),
                    'provider' => $provider,
                    'provider_uid' => $providerUid,
                ],
            ];
        }

        if (! $user) {
            $user = $this->createSocialUser($provider, $providerUid, $claims, $profile);
        } else {
            $this->updateSocialUser($user, $provider, $providerUid, $claims, $profile);
        }

        return $this->issueTokenForUser($user, $profile['fcmToken'] ?? null);
    }

    protected function findSocialUser(string $provider, string $providerUid, ?string $email): ?AppUser
    {
        $user = AppUser::query()
            ->where('role', 'customer')
            ->where('payload->provider', $provider)
            ->where('payload->provider_uid', $providerUid)
            ->first();

        if ($user) {
            return $user;
        }

        if ($email) {
            return AppUser::query()
                ->where('role', 'customer')
                ->where('email', $email)
                ->first();
        }

        return null;
    }

    protected function createSocialUser(string $provider, string $providerUid, array $claims, array $profile): AppUser
    {
        $email = $claims['email'] ?? $profile['email'] ?? null;

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => ['Email is required to complete social registration.'],
            ]);
        }

        if (AppUser::query()->where('email', $email)->where('role', '!=', 'customer')->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered with another account type.'],
            ]);
        }

        $user = AppUser::query()->create([
            'id' => $profile['id'] ?? (string) Str::uuid(),
            'firstName' => $profile['firstName'] ?? $claims['given_name'] ?? $this->firstNameFromName($claims['name'] ?? null),
            'lastName' => $profile['lastName'] ?? $claims['family_name'] ?? $this->lastNameFromName($claims['name'] ?? null),
            'email' => $email,
            'phoneNumber' => $profile['phoneNumber'] ?? null,
            'countryCode' => $profile['countryCode'] ?? null,
            'profilePictureURL' => $claims['picture'] ?? $profile['profilePictureURL'] ?? null,
            'role' => 'customer',
            'active' => true,
            'isActive' => true,
            'fcmToken' => $profile['fcmToken'] ?? null,
            'wallet_amount' => 0,
            'createdAt' => now(),
            'payload' => [
                'provider' => $provider,
                'provider_uid' => $providerUid,
            ],
        ]);

        return $user->fresh();
    }

    protected function updateSocialUser(AppUser $user, string $provider, string $providerUid, array $claims, array $profile): void
    {
        $updates = [];

        if (! empty($profile['fcmToken'])) {
            $updates['fcmToken'] = $profile['fcmToken'];
        }

        if (! empty($profile['firstName'])) {
            $updates['firstName'] = $profile['firstName'];
        }

        if (! empty($profile['lastName'])) {
            $updates['lastName'] = $profile['lastName'];
        }

        if (! empty($claims['picture']) && empty($user->profilePictureURL)) {
            $updates['profilePictureURL'] = $claims['picture'];
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        $user->mergePayload([
            'provider' => $provider,
            'provider_uid' => $providerUid,
        ]);
        $user->save();
    }

    protected function issueTokenForUser(AppUser $user, ?string $fcmToken = null): array
    {
        if (! $user->active || ! $user->isActive) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive.'],
            ]);
        }

        if ($fcmToken) {
            $user->update(['fcmToken' => $fcmToken]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('customer-api', ['customer'])->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token, 'is_new_user' => false];
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
