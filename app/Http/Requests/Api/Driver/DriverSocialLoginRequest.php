<?php

namespace App\Http\Requests\Api\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverSocialLoginRequest extends FormRequest
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
            'serviceType' => ['nullable', 'string', 'max:64'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'isOwner' => ['nullable', 'boolean'],
            'auto_register' => ['nullable', 'boolean'],
            'profilePictureURL' => ['nullable', 'string'],
        ];
    }
}
