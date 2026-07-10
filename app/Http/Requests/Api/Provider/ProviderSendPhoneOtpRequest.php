<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderSendPhoneOtpRequest extends FormRequest
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
        ];
    }
}
