<?php

namespace App\Services\Worker;

use App\Mail\WorkerPasswordResetMail;
use App\Models\AppUser;
use App\Models\ProviderWorker;
use App\Services\Auth\AppAuthService;
use App\Support\CatalogEntityWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkerAuthService
{
    public function __construct(protected AppAuthService $authService)
    {
    }

    /**
     * Optional register — Flutter app does not self-register.
     * Creates providers_workers + synced app_users (role=worker).
     */
    public function register(array $data): array
    {
        if (empty($data['providerId'])) {
            throw ValidationException::withMessages([
                'providerId' => ['providerId is required. Workers must belong to a provider.'],
            ]);
        }

        $provider = AppUser::query()
            ->where('id', $data['providerId'])
            ->where('role', 'provider')
            ->first();

        if (! $provider) {
            throw ValidationException::withMessages([
                'providerId' => ['Provider not found.'],
            ]);
        }

        $id = $data['id'] ?? (string) Str::uuid();
        $password = $data['password'];

        $worker = CatalogEntityWriter::write(new ProviderWorker(), [
            'id' => $id,
            'firstName' => $data['firstName'] ?? $data['first_name'],
            'lastName' => $data['lastName'] ?? $data['last_name'] ?? null,
            'email' => $data['email'],
            'phoneNumber' => $data['phoneNumber'] ?? null,
            'address' => $data['address'] ?? null,
            'salary' => $data['salary'] ?? null,
            'providerId' => $provider->id,
            'active' => true,
            'online' => false,
            'reviewsCount' => 0,
            'reviewsSum' => 0,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'profilePictureURL' => $data['profilePictureURL'] ?? null,
            'fcmToken' => $data['fcmToken'] ?? null,
            'password_hash' => Hash::make($password),
            'createdAt' => now(),
        ]);

        $user = $this->syncAppUser($worker, $password, $data['fcmToken'] ?? null);
        $token = $user->createToken('worker-api', ['worker'])->plainTextToken;

        return [
            'worker' => $worker->fresh(),
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(string $email, string $password, ?string $fcmToken = null): array
    {
        $worker = ProviderWorker::query()
            ->where(function ($q) use ($email) {
                $q->where('email', $email)->orWhere('payload->email', $email);
            })
            ->first();

        if (! $worker) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $doc = $worker->toDocumentArray();
        $passwordHash = $doc['password_hash'] ?? null;

        $user = AppUser::query()
            ->where('id', $worker->id)
            ->where('role', 'worker')
            ->first();

        $valid = false;
        if ($passwordHash && Hash::check($password, $passwordHash)) {
            $valid = true;
        } elseif ($user && $user->password && Hash::check($password, $user->password)) {
            $valid = true;
            // Backfill password_hash on worker doc for consistency
            $worker->mergePayload(['password_hash' => $user->password]);
            $worker->save();
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $active = (bool) ($doc['active'] ?? $doc['isActive'] ?? true);
        if (! $active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated. Please contact the administrator.'],
            ]);
        }

        if ($fcmToken) {
            $worker->mergePayload(['fcmToken' => $fcmToken]);
            $worker->save();
        }

        $user = $this->syncAppUser($worker->fresh(), null, $fcmToken);
        $user->tokens()->delete();
        $token = $user->createToken('worker-api', ['worker'])->plainTextToken;

        return [
            'worker' => $worker->fresh(),
            'user' => $user,
            'token' => $token,
        ];
    }

    public function forgotPassword(string $email): void
    {
        $worker = ProviderWorker::query()
            ->where(function ($q) use ($email) {
                $q->where('email', $email)->orWhere('payload->email', $email);
            })
            ->first();

        if (! $worker) {
            return;
        }

        $user = $this->syncAppUser($worker);
        $plainToken = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        Mail::to($email)->send(new WorkerPasswordResetMail($user, $plainToken));
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

        $worker = ProviderWorker::query()
            ->where(function ($q) use ($email) {
                $q->where('email', $email)->orWhere('payload->email', $email);
            })
            ->first();

        if (! $worker) {
            throw ValidationException::withMessages([
                'email' => ['No worker account found for this email.'],
            ]);
        }

        $hash = Hash::make($password);
        $worker->mergePayload(['password_hash' => $hash]);
        $worker->save();

        $user = $this->syncAppUser($worker, $password);
        $user->tokens()->delete();
        DB::table('password_resets')->where('email', $email)->delete();
    }

    public function logout(AppUser $user): void
    {
        $this->authService->logout($user);
    }

    public function deleteAccount(AppUser $user): void
    {
        $worker = ProviderWorker::query()->find($user->id);

        $user->tokens()->delete();
        $user->update([
            'active' => false,
            'isActive' => false,
            'fcmToken' => null,
            'email' => 'deleted_' . $user->id . '_' . ($user->email ?? ''),
        ]);

        if ($worker) {
            $worker->mergePayload([
                'active' => false,
                'online' => false,
                'fcmToken' => null,
                'deleted_at' => now()->toIso8601String(),
            ]);
            $worker->save();
        }
    }

    public function resolveWorker(AppUser $user): ?ProviderWorker
    {
        if ($user->role !== 'worker') {
            return null;
        }

        return ProviderWorker::query()->find($user->id);
    }

    /**
     * Keep app_users in sync so Sanctum + app.role:worker middleware work.
     */
    public function syncAppUser(ProviderWorker $worker, ?string $plainPassword = null, ?string $fcmToken = null): AppUser
    {
        $doc = $worker->toDocumentArray();

        $attributes = [
            'firstName' => $doc['firstName'] ?? null,
            'lastName' => $doc['lastName'] ?? null,
            'email' => $doc['email'] ?? ($worker->id . '@worker.local'),
            'phoneNumber' => $doc['phoneNumber'] ?? null,
            'role' => 'worker',
            'active' => (bool) ($doc['active'] ?? true),
            'isActive' => (bool) ($doc['active'] ?? $doc['isActive'] ?? true),
            'profilePictureURL' => $doc['profilePictureURL'] ?? null,
            'latitude' => $doc['latitude'] ?? null,
            'longitude' => $doc['longitude'] ?? null,
            'fcmToken' => $fcmToken ?? ($doc['fcmToken'] ?? null),
            'ownerId' => $doc['providerId'] ?? null,
            'createdAt' => $worker->createdAt ?? now(),
        ];

        if ($plainPassword) {
            $attributes['password'] = Hash::make($plainPassword);
        }

        $user = AppUser::query()->find($worker->id);

        if ($user) {
            // Don't overwrite password unless provided
            if (! $plainPassword) {
                unset($attributes['password']);
            }
            $user->update($attributes);
            $user->mergePayload([
                'providerId' => $doc['providerId'] ?? null,
                'salary' => $doc['salary'] ?? null,
                'online' => $doc['online'] ?? false,
            ]);
            $user->save();

            return $user->fresh();
        }

        if (! isset($attributes['password'])) {
            $attributes['password'] = $doc['password_hash'] ?? Hash::make(Str::random(32));
        }

        return AppUser::query()->create(array_merge($attributes, [
            'id' => $worker->id,
            'wallet_amount' => 0,
            'payload' => [
                'providerId' => $doc['providerId'] ?? null,
                'salary' => $doc['salary'] ?? null,
                'online' => $doc['online'] ?? false,
            ],
        ]));
    }
}
