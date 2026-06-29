<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'role' => $this->role,
            'active' => (bool) $this->active,
            'isActive' => (bool) $this->isActive,
            'profilePictureURL' => $this->profilePictureURL,
            'sectionId' => $this->sectionId ?? $this->section_id,
            'wallet_amount' => (float) $this->wallet_amount,
            'fcmToken' => $this->when($request->user()?->id === $this->id, $this->fcmToken),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'createdAt' => optional($this->createdAt)->toIso8601String(),
        ];
    }
}
