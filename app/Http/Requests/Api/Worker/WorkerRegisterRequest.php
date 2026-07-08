<?php

namespace App\Http\Requests\Api\Worker;

use Illuminate\Foundation\Http\FormRequest;

class WorkerRegisterRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'providerId' => ['required', 'string', 'max:64'],
            'address' => ['nullable', 'string'],
            'salary' => ['nullable'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'fcmToken' => ['nullable', 'string'],
            'profilePictureURL' => ['nullable', 'string'],
        ];
    }
}
