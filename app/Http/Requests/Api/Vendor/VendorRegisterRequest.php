<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName' => ['required_without:first_name', 'string', 'max:120'],
            'first_name' => ['required_without:firstName', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('app_users', 'email')->where('role', 'vendor')],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'zoneId' => ['nullable', 'string', 'max:64'],
            'fcmToken' => ['nullable', 'string'],
            'appIdentifier' => ['nullable', 'string', 'max:32'],
        ];
    }
}
