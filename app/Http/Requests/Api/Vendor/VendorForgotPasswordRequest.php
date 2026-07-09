<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }
}
