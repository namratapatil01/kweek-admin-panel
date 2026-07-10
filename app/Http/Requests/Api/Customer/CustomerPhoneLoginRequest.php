<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPhoneLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phoneNumber' => ['required_without:phone', 'string', 'max:32'],
            'phone' => ['required_without:phoneNumber', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'fcmToken' => ['nullable', 'string'],
            'firstName' => ['nullable', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'auto_register' => ['nullable', 'boolean'],
        ];
    }
}
