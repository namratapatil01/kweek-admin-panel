<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderProfileUpdateRequest extends FormRequest
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
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'street' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'fcmToken' => ['nullable', 'string'],
            'userBankDetails' => ['nullable', 'array'],
            'userBankDetails.bankName' => ['nullable', 'string'],
            'userBankDetails.branchName' => ['nullable', 'string'],
            'userBankDetails.holderName' => ['nullable', 'string'],
            'userBankDetails.accountNumber' => ['nullable', 'string'],
            'userBankDetails.otherDetails' => ['nullable', 'string'],
        ];
    }
}
