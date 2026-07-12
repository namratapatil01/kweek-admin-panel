<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderChatSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderId' => ['required', 'string', 'max:64'],
            'receiverId' => ['nullable', 'string', 'max:64'],
            'customerId' => ['nullable', 'string', 'max:64'],
            'message' => ['required_without:url', 'string'],
            'messageType' => ['nullable', 'string', 'max:32'],
            'chatType' => ['nullable', 'string', 'in:customer,worker,driver,store'],
            'type' => ['nullable', 'string', 'in:customer,worker,driver,store'],
            'url' => ['nullable', 'array'],
            'videoThumbnail' => ['nullable', 'string'],
            'customerName' => ['nullable', 'string'],
            'customerProfileImage' => ['nullable', 'string'],
            'restaurantName' => ['nullable', 'string'],
            'restaurantProfileImage' => ['nullable', 'string'],
        ];
    }
}
