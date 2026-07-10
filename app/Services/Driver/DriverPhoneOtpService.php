<?php

namespace App\Services\Driver;

use App\Models\PhoneOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DriverPhoneOtpService
{
    public function sendOtp(string $phoneNumber, ?string $countryCode = null): array
    {
        $plainOtp = (string) random_int(100000, 999999);
        $verificationId = (string) Str::uuid();
        $expiresIn = 300;

        PhoneOtp::query()
            ->where('phone_number', $phoneNumber)
            ->where('country_code', $countryCode)
            ->where('role', 'driver')
            ->delete();

        PhoneOtp::query()->create([
            'id' => $verificationId,
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'otp_hash' => Hash::make($plainOtp),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($expiresIn),
            'role' => 'driver',
        ]);

        if (config('app.debug')) {
            Log::info('Driver OTP generated', [
                'phone' => $phoneNumber,
                'otp' => $plainOtp,
                'verificationId' => $verificationId,
            ]);
        }

        return [
            'verificationId' => $verificationId,
            'expiresIn' => $expiresIn,
            'debug_otp' => config('app.debug') ? $plainOtp : null,
        ];
    }

    public function verifyOtp(string $verificationId, string $otp): void
    {
        $record = PhoneOtp::query()->find($verificationId);

        if (! $record) {
            throw ValidationException::withMessages(['otp' => ['Invalid verification session.']]);
        }

        if ($record->expires_at->isPast()) {
            $record->delete();
            throw ValidationException::withMessages(['otp' => ['OTP has expired.']]);
        }

        if ($record->attempts >= 5) {
            $record->delete();
            throw ValidationException::withMessages(['otp' => ['Too many attempts. Request a new OTP.']]);
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            $record->increment('attempts');
            throw ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
        }

        $record->delete();
    }
}
