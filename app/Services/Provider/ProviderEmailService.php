<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProviderEmailService
{
    public function sendPayoutRequestEmail(AppUser $provider, float $amount, string $payoutRequestId): void
    {
        $template = EmailTemplate::query()->where('type', 'payout_request')->first();

        if (! $template || ! $provider->email) {
            return;
        }

        $doc = $template->toDocumentArray();
        $name = trim(($provider->firstName ?? '') . ' ' . ($provider->lastName ?? ''));
        $body = str_replace(
            ['{username}', '{userid}', '{amount}', '{payoutrequestid}', '{usercontactinfo}', '{date}'],
            [
                $name,
                $provider->id,
                $amount,
                $payoutRequestId,
                ($provider->email ?? '') . "\n" . ($provider->phoneNumber ?? ''),
                now()->format('d-m-Y'),
            ],
            (string) ($doc['message'] ?? '')
        );

        $subject = str_replace(
            ['{userid}'],
            [$provider->id],
            (string) ($doc['subject'] ?? 'Payout Request')
        );

        $recipients = [$provider->email];

        if (! empty($doc['isSendToAdmin'])) {
            $adminEmail = config('mail.from.address');
            if ($adminEmail) {
                $recipients[] = $adminEmail;
            }
        }

        try {
            Mail::raw($body, function ($message) use ($recipients, $subject) {
                $message->to($recipients)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Provider payout email failed', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function emailTemplate(string $type): ?array
    {
        $template = EmailTemplate::query()->where('type', $type)->first();

        return $template?->toDocumentArray();
    }
}
