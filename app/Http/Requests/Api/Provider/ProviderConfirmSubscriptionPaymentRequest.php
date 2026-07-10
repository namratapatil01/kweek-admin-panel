<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderConfirmSubscriptionPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required_without:subscriptionPlanId', 'string', 'max:64'],
            'subscriptionPlanId' => ['required_without:plan_id', 'string', 'max:64'],
            'payment_type' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', 'max:64'],
            'payment_id' => ['required_without:paymentId', 'string', 'max:255'],
            'paymentId' => ['required_without:payment_id', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
