<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payload = is_array($this->payload) ? $this->payload : [];

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
            'section_id' => $this->section_id ?? $this->sectionId,
            'wallet_amount' => (float) ($this->wallet_amount ?? 0),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'street' => $payload['street'] ?? null,
            'area' => $payload['area'] ?? null,
            'userBankDetails' => $this->userBankDetails ?? [],
            'reviewsCount' => (int) ($payload['reviewsCount'] ?? 0),
            'reviewsSum' => (float) ($payload['reviewsSum'] ?? 0),
            'adminCommission' => $payload['adminCommission'] ?? null,
            'subscriptionPlanId' => $payload['subscriptionPlanId'] ?? null,
            'subscriptionExpiryDate' => $payload['subscriptionExpiryDate'] ?? null,
            'subscription_plan' => $payload['subscription_plan'] ?? null,
            'subscriptionTotalOrders' => $payload['subscriptionTotalOrders'] ?? null,
            'fcmToken' => $this->when($request->user()?->id === $this->id, $this->fcmToken),
            'provider' => $payload['provider'] ?? null,
            'createdAt' => optional($this->createdAt)->toIso8601String(),
        ];
    }
}
