<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderSocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
            'fcmToken' => ['nullable', 'string'],
            'firstName' => ['nullable', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'auto_register' => ['nullable', 'boolean'],
            'profilePictureURL' => ['nullable', 'string'],
        ];
    }
}
