<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderPhoneLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phoneNumber' => ['required', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'firstName' => ['nullable', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'fcmToken' => ['nullable', 'string'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'auto_register' => ['nullable', 'boolean'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ];
    }
}
