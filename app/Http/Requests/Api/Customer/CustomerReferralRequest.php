<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'referralCode' => ['required', 'string', 'max:64'],
            'referralBy' => ['nullable', 'string', 'max:64'],
            'isSuccessful' => ['nullable', 'boolean'],
        ];
    }
}
