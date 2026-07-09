<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerSocialLoginRequest extends FormRequest
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
            'email' => ['nullable', 'email', 'max:255'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'auto_register' => ['nullable', 'boolean'],
        ];
    }
}
