<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderVerifyPhoneOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'verificationId' => ['required', 'uuid'],
            'otp' => ['required', 'string', 'size:6'],
            'phoneNumber' => ['required', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'firstName' => ['nullable', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'fcmToken' => ['nullable', 'string'],
            'auto_register' => ['nullable', 'boolean'],
            'sectionId' => ['nullable', 'string', 'max:64'],
        ];
    }
}
