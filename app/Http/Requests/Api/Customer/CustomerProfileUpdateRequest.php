<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'zoneId' => ['nullable', 'string', 'max:64'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'shippingAddress' => ['nullable', 'array'],
            'fcmToken' => ['nullable', 'string'],
        ];
    }
}
