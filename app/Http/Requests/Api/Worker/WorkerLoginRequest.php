<?php

namespace App\Http\Requests\Api\Worker;

use Illuminate\Foundation\Http\FormRequest;

class WorkerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'fcmToken' => ['nullable', 'string'],
        ];
    }
}
