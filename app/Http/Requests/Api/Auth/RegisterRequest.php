<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:app_users,email'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', 'in:customer,driver,vendor,provider,owner,worker'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'fcmToken' => ['nullable', 'string'],
        ];
    }
}
