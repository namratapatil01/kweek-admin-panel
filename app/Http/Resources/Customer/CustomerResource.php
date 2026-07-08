<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'countryCode' => $this->countryCode,
            'profilePictureURL' => $this->profilePictureURL,
            'role' => $this->role,
            'active' => (bool) $this->active,
            'isActive' => (bool) $this->isActive,
            'sectionId' => $this->sectionId ?? $this->section_id,
            'zoneId' => $this->zoneId,
            'wallet_amount' => (float) ($this->wallet_amount ?? 0),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'shippingAddress' => $this->shippingAddress ?? [],
            'fcmToken' => $this->when($request->user()?->id === $this->id, $this->fcmToken),
            'provider' => $this->payload['provider'] ?? null,
            'createdAt' => optional($this->createdAt)->toIso8601String(),
        ];
    }
}
