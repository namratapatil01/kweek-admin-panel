<?php

namespace App\Http\Resources\Vendor;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function __construct($resource, protected ?Vendor $store = null)
    {
        parent::__construct($resource);
    }

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
            'isDocumentVerify' => (bool) $this->isDocumentVerify,
            'vendorID' => $this->vendorID,
            'sectionId' => $this->sectionId ?? $this->section_id,
            'section_id' => $this->section_id ?? $this->sectionId,
            'zoneId' => $this->zoneId,
            'wallet_amount' => (float) ($this->wallet_amount ?? 0),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'userBankDetails' => $this->userBankDetails ?? [],
            'reviewsCount' => (int) ($payload['reviewsCount'] ?? 0),
            'reviewsSum' => (float) ($payload['reviewsSum'] ?? 0),
            'subscriptionPlanId' => $payload['subscriptionPlanId'] ?? null,
            'subscriptionExpiryDate' => $payload['subscriptionExpiryDate'] ?? null,
            'subscription_plan' => $payload['subscription_plan'] ?? null,
            'subscriptionTotalOrders' => $payload['subscriptionTotalOrders'] ?? null,
            'adminCommission' => $payload['adminCommission'] ?? null,
            'provider' => $payload['provider'] ?? null,
            'appIdentifier' => $payload['appIdentifier'] ?? null,
            'fcmToken' => $this->when($request->user()?->id === $this->id, $this->fcmToken),
            'createdAt' => optional($this->createdAt)->toIso8601String(),
            'store' => $this->when($this->store !== null, fn () => $this->store->toDocumentArray()),
        ];
    }
}
