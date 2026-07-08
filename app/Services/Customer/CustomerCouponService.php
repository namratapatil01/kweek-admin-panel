<?php

namespace App\Services\Customer;

use App\Models\Coupon;
use App\Models\ParcelCoupon;
use App\Models\Promo;
use App\Models\ProviderCoupon;
use App\Models\RentalCoupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerCouponService
{
    public function list(string $type, ?string $sectionId = null, ?string $vendorId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = match ($type) {
            'parcel' => ParcelCoupon::query(),
            'rental' => RentalCoupon::query(),
            'provider' => ProviderCoupon::query(),
            'cab' => Promo::query(),
            default => Coupon::query(),
        };

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhere('sectionId', $sectionId);
            });
        }

        if ($vendorId && $type === 'vendor') {
            $query->where('vendorID', $vendorId);
        }

        if ($vendorId && $type === 'provider') {
            $query->where('providerId', $vendorId);
        }

        return $query
            ->where('isEnabled', true)
            ->where('expiresAt', '>=', now())
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
