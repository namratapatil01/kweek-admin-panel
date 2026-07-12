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
            if (in_array($type, ['parcel', 'rental', 'provider', 'cab'], true)) {
                $query->where('sectionId', $sectionId);
            } else {
                $query->where('section_id', $sectionId);
            }
        }

        if ($vendorId && $type === 'vendor') {
            $query->where('vendorID', $vendorId);
        }

        if ($vendorId && $type === 'provider') {
            $query->where('providerId', $vendorId);
        }

        return $query
            ->where(function ($q) {
                $q->where('isEnabled', true)
                    ->orWhere('isEnable', true)
                    ->orWhere('payload->isEnabled', true)
                    ->orWhere('payload->isEnable', true);
            })
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }
}
