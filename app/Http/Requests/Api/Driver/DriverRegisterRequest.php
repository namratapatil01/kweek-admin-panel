<?php

namespace App\Http\Requests\Api\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'max:64'],
            'firstName' => ['required_without:first_name', 'string', 'max:120'],
            'first_name' => ['required_without:firstName', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('app_users', 'email')->where('role', 'driver')],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'serviceType' => ['nullable', 'string', 'max:64'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'zoneId' => ['nullable', 'string', 'max:64'],
            'vendorID' => ['nullable', 'string', 'max:64'],
            'isOwner' => ['nullable', 'boolean'],
            'ownerId' => ['nullable', 'string', 'max:64'],
            'carName' => ['nullable', 'string', 'max:120'],
            'carNumber' => ['nullable', 'string', 'max:64'],
            'carMakes' => ['nullable', 'string', 'max:120'],
            'vehicleType' => ['nullable', 'string', 'max:64'],
            'vehicleId' => ['nullable', 'string', 'max:64'],
            'rideType' => ['nullable', 'string', 'max:64'],
            'fcmToken' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'profilePictureURL' => ['nullable', 'string'],
            'location' => ['nullable', 'array'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
        ];
    }
}
