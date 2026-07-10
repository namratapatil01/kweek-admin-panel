<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DriverEmailService
{
    public function sendWalletTopUpEmail(AppUser $driver, float $amount, string $paymentMethod, string $transactionId): void
    {
        $template = EmailTemplate::query()->where('type', 'wallet_topup')->first();

        if (! $template || ! $driver->email) {
            return;
        }

        $doc = $template->toDocumentArray();
        $name = trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? ''));
        $body = str_replace(
            ['{username}', '{date}', '{amount}', '{paymentmethod}', '{transactionid}', '{newwalletbalance}.'],
            [$name, now()->toDateString(), $amount, $paymentMethod, $transactionId, (float) ($driver->wallet_amount ?? 0)],
            (string) ($doc['message'] ?? '')
        );

        try {
            Mail::raw($body, function ($message) use ($driver, $doc) {
                $message->to($driver->email)->subject((string) ($doc['subject'] ?? 'Wallet Top-up'));
            });
        } catch (\Throwable $e) {
            Log::warning('Driver wallet top-up email failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
        }
    }

    public function sendPayoutRequestEmail(AppUser $driver, float $amount, string $payoutRequestId): void
    {
        $template = EmailTemplate::query()->where('type', 'payout_request')->first();

        if (! $template || ! $driver->email) {
            return;
        }

        $doc = $template->toDocumentArray();
        $name = trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? ''));
        $body = str_replace(
            ['{username}', '{userid}', '{amount}', '{payoutrequestid}', '{usercontactinfo}'],
            [$name, $driver->id, $amount, $payoutRequestId, ($driver->email ?? '') . "\n" . ($driver->phoneNumber ?? '')],
            (string) ($doc['message'] ?? '')
        );

        try {
            Mail::raw($body, function ($message) use ($driver, $doc) {
                $message->to($driver->email)->subject((string) ($doc['subject'] ?? 'Payout Request'));
            });
        } catch (\Throwable $e) {
            Log::warning('Driver payout email failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
        }
    }
}
