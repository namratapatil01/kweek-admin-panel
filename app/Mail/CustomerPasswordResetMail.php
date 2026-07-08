<?php

namespace App\Mail;

use App\Models\AppUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable
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
            ->subject('Reset your KWEEK password')
            ->view('mail.customer-password-reset');
    }
}
