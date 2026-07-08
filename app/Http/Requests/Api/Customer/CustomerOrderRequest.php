<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:vendor,parcel,rental,ride,provider,dine-in'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:64'],
            'vendorID' => ['nullable', 'string', 'max:64'],
            'driverID' => ['nullable', 'string', 'max:64'],
            'driverId' => ['nullable', 'string', 'max:64'],
            'workerId' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', 'max:64'],
            'paymentMethod' => ['nullable', 'string', 'max:64'],
            'products' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
            'subTotal' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'deliveryCharge' => ['nullable', 'numeric'],
            'couponCode' => ['nullable', 'string', 'max:64'],
            'takeAway' => ['nullable', 'boolean'],
            'scheduleTime' => ['nullable', 'date'],
            'scheduleDateTime' => ['nullable', 'date'],
        ];
    }
}
