<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'isDocumentVerify' => (bool) $this->isDocumentVerify,
            'isOwner' => (bool) $this->isOwner,
            'ownerId' => $this->ownerId,
            'serviceType' => $this->serviceType,
            'sectionId' => $this->sectionId ?? $this->section_id,
            'section_id' => $this->section_id ?? $this->sectionId,
            'zoneId' => $this->zoneId,
            'vendorID' => $this->vendorID,
            'wallet_amount' => (float) ($this->wallet_amount ?? 0),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'rotation' => $payload['rotation'] ?? null,
            'carName' => $this->carName,
            'carNumber' => $this->carNumber,
            'carPictureURL' => $this->carPictureURL,
            'carMakes' => $this->carMakes,
            'vehicleType' => $this->vehicleType,
            'vehicleId' => $this->vehicleId,
            'rideType' => $this->rideType,
            'userBankDetails' => $this->userBankDetails ?? [],
            'reviewsCount' => (int) ($payload['reviewsCount'] ?? 0),
            'reviewsSum' => (float) ($payload['reviewsSum'] ?? 0),
            'adminCommission' => $payload['adminCommission'] ?? null,
            'orderRequestData' => $payload['orderRequestData'] ?? [],
            'inProgressOrderID' => $payload['inProgressOrderID'] ?? [],
            'orderCabRequestData' => $payload['orderCabRequestData'] ?? null,
            'provider' => $payload['provider'] ?? null,
            'fcmToken' => $this->when($request->user()?->id === $this->id, $this->fcmToken),
            'createdAt' => optional($this->createdAt)->toIso8601String(),
        ];
    }
}
