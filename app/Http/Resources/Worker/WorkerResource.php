<?php

namespace App\Http\Resources\Worker;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $doc = method_exists($this->resource, 'toDocumentArray')
            ? $this->resource->toDocumentArray()
            : (array) $this->resource;

        return [
            'id' => $doc['id'] ?? null,
            'firstName' => $doc['firstName'] ?? null,
            'lastName' => $doc['lastName'] ?? null,
            'email' => $doc['email'] ?? null,
            'phoneNumber' => $doc['phoneNumber'] ?? null,
            'address' => $doc['address'] ?? null,
            'salary' => $doc['salary'] ?? null,
            'providerId' => $doc['providerId'] ?? null,
            'active' => (bool) ($doc['active'] ?? $doc['isActive'] ?? false),
            'isActive' => (bool) ($doc['isActive'] ?? $doc['active'] ?? false),
            'online' => (bool) ($doc['online'] ?? false),
            'profilePictureURL' => $doc['profilePictureURL'] ?? null,
            'latitude' => $doc['latitude'] ?? null,
            'longitude' => $doc['longitude'] ?? null,
            'location' => [
                'latitude' => $doc['latitude'] ?? null,
                'longitude' => $doc['longitude'] ?? null,
            ],
            'g' => $doc['g'] ?? null,
            'fcmToken' => $this->when(
                $request->user()?->id === ($doc['id'] ?? null),
                $doc['fcmToken'] ?? null
            ),
            'reviewsCount' => (int) ($doc['reviewsCount'] ?? 0),
            'reviewsSum' => (float) ($doc['reviewsSum'] ?? 0),
            'role' => 'worker',
            'createdAt' => $doc['createdAt'] ?? null,
        ];
    }
}
