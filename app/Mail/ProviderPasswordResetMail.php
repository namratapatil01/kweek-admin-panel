<?php

namespace App\Mail;

use App\Models\AppUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AppUser $user,
        public string $token
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Reset your KWEEK provider password')
            ->view('mail.provider-password-reset');
    }
}
