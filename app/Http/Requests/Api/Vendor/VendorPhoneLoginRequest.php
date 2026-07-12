<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorPhoneLoginRequest extends FormRequest
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
            'email' => ['nullable', 'email'],
            'auto_register' => ['nullable', 'boolean'],
        ];
    }
}
