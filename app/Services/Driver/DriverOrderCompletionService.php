<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\VendorOrder;
use App\Models\Wallet;
use App\Services\Notifications\FcmNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DriverOrderCompletionService
{
    public function __construct(
        protected DriverWalletService $walletService,
        protected DriverReferralService $referralService,
        protected FcmNotificationService $fcmService
    ) {
    }

    public function afterComplete(AppUser $driver, Model $order, string $type, array $extra = []): void
    {
        match ($type) {
            'vendor' => $this->completeVendor($driver, $order),
            'ride' => $this->completeRide($driver, $order),
            'parcel' => $this->completeParcel($driver, $order),
            'rental' => $this->completeRental($driver, $order, $extra),
            default => null,
        };

        $this->referralService->processReferralReward($order, $type);
        $this->notifyCustomer($order, $type);
    }

    protected function completeVendor(AppUser $driver, Model $order): void
    {
        /** @var VendorOrder $order */
        $doc = $order->toDocumentArray();
        $paymentMethod = strtolower((string) ($doc['paymentMethod'] ?? $doc['payment_method'] ?? ''));

        $subTotal = $this->calculateVendorProductAmount($order);
        $deliveryCharge = (float) ($doc['deliveryCharge'] ?? $doc['delivery_charge'] ?? data_get($doc, 'payload.deliveryCharge', 0));
        $tip = (float) ($doc['tipAmount'] ?? $doc['tip_amount'] ?? 0);
        $deliveryCredit = $deliveryCharge + $tip;

        $walletUserId = $this->resolveWalletUserId($driver);

        if ($paymentMethod === 'cod' && $subTotal > 0) {
            $this->debitWallet($walletUserId, $subTotal, $order->id, 'COD product amount debited');
        }

        if ($deliveryCredit > 0) {
            $this->creditWallet($walletUserId, $deliveryCredit, $order->id, 'Delivery charge credited');
        }

        $this->creditCustomerCashback($order);
    }

    protected function completeRide(AppUser $driver, Model $order): void
    {
        /** @var Ride $order */
        $doc = $order->toDocumentArray();
        $amount = (float) ($doc['subTotal'] ?? 0);
        $walletUserId = $this->resolveWalletUserId($driver);

        $commission = (float) ($doc['adminCommission'] ?? 0);
        $commissionType = strtolower((string) ($doc['adminCommissionType'] ?? 'percentage'));

        if ($commission > 0) {
            $amount -= $commissionType === 'percentage'
                ? ($amount * $commission / 100)
                : $commission;
        }

        if ($amount > 0) {
            $this->creditWallet($walletUserId, $amount, $order->id, 'Ride fare credited');
        }
    }

    protected function completeParcel(AppUser $driver, Model $order): void
    {
        /** @var ParcelOrder $order */
        $doc = $order->toDocumentArray();
        $amount = (float) ($doc['subTotal'] ?? 0);
        $walletUserId = $this->resolveWalletUserId($driver);

        if ($amount > 0) {
            $this->creditWallet($walletUserId, $amount, $order->id, 'Parcel delivery credited');
        }
    }

    protected function completeRental(AppUser $driver, Model $order, array $extra = []): void
    {
        /** @var RentalOrder $order */
        $doc = $order->toDocumentArray();
        $payload = $order->payload ?? [];

        if (isset($extra['endKilometerReading'])) {
            $payload['endKitoMetersReading'] = $extra['endKilometerReading'];
        }

        $subTotal = (float) ($doc['subTotal'] ?? 0);
        $discount = (float) ($doc['discount'] ?? 0);
        $extraKmCharge = $this->calculateRentalExtraKm($doc, $payload);
        $extraMinCharge = $this->calculateRentalExtraMinutes($doc, $payload);
        $subTotal += $extraKmCharge + $extraMinCharge;

        $taxAmount = 0.0;
        foreach ($doc['taxSetting'] ?? [] as $tax) {
            if (is_array($tax)) {
                $taxAmount += $this->calculateTaxAmount($subTotal - $discount, $tax);
            }
        }

        $totalAmount = ($subTotal - $discount) + $taxAmount;
        $paymentMethod = strtolower((string) ($doc['paymentMethod'] ?? $doc['payment_method'] ?? ''));
        $walletUserId = $this->resolveWalletUserId($driver);

        if ($paymentMethod !== 'cod' && $totalAmount > 0) {
            $this->creditWallet($walletUserId, $totalAmount, $order->id, 'Rental booking credited');
        }

        $commission = (float) ($doc['adminCommission'] ?? 0);
        $commissionType = strtolower((string) ($doc['adminCommissionType'] ?? 'percentage'));

        if ($commission > 0) {
            $commissionAmount = $commissionType === 'percentage'
                ? ($totalAmount * $commission / 100)
                : $commission;

            if ($commissionAmount > 0) {
                $this->debitWallet($walletUserId, $commissionAmount, $order->id, 'Admin commission deducted');
            }
        }

        $order->update(['payload' => $payload]);
    }

    protected function calculateRentalExtraKm(array $doc, array $payload): float
    {
        $package = $doc['rentalPackageModel'] ?? data_get($doc, 'payload.rentalPackageModel', []);
        $startKm = (float) ($payload['startKitoMetersReading'] ?? $doc['startKitoMetersReading'] ?? 0);
        $endKm = (float) ($payload['endKitoMetersReading'] ?? $doc['endKitoMetersReading'] ?? 0);

        if ($endKm <= $startKm) {
            return 0;
        }

        $totalKm = $endKm - $startKm;
        $included = (float) ($package['includedDistance'] ?? 0);
        $extraRate = (float) ($package['extraKmFare'] ?? 0);

        if ($totalKm > $included && $extraRate > 0) {
            return ($totalKm - $included) * $extraRate;
        }

        return 0;
    }

    protected function calculateRentalExtraMinutes(array $doc, array $payload): float
    {
        $package = $doc['rentalPackageModel'] ?? data_get($doc, 'payload.rentalPackageModel', []);
        $startTime = $payload['startTime'] ?? $doc['startTime'] ?? null;
        $endTime = $payload['endTime'] ?? $doc['endTime'] ?? now()->toIso8601String();

        if (! $startTime) {
            return 0;
        }

        $start = strtotime((string) $startTime);
        $end = strtotime((string) $endTime);
        $hours = (int) floor(max(0, $end - $start) / 3600);
        $includedHours = (int) ($package['includedHours'] ?? 0);
        $minuteRate = (float) ($package['extraMinuteFare'] ?? 0);

        if ($hours > $includedHours && $minuteRate > 0) {
            return (($hours - $includedHours) * 60) * $minuteRate;
        }

        return 0;
    }

    protected function calculateVendorProductAmount(VendorOrder $order): float
    {
        $doc = $order->toDocumentArray();
        $products = $doc['products'] ?? [];
        $subTotal = 0.0;

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $qty = (float) ($product['quantity'] ?? 1);
            $price = (float) ($product['discountPrice'] ?? 0) > 0
                ? (float) $product['discountPrice']
                : (float) ($product['price'] ?? 0);
            $extras = (float) ($product['extrasPrice'] ?? 0);

            $subTotal += ($price + $extras) * $qty;
        }

        $specialDiscount = (float) data_get($doc, 'specialDiscount.special_discount', 0);
        $discount = (float) ($doc['discount'] ?? 0);
        $taxAmount = 0.0;

        foreach ($doc['taxSetting'] ?? [] as $tax) {
            if (is_array($tax)) {
                $taxAmount += $this->calculateTaxAmount($subTotal - $discount - $specialDiscount, $tax);
            }
        }

        return $subTotal - $discount - $specialDiscount + $taxAmount;
    }

    protected function calculateTaxAmount(float $amount, array $tax): float
    {
        $type = strtolower((string) ($tax['type'] ?? 'percentage'));
        $value = (float) ($tax['tax'] ?? $tax['value'] ?? 0);

        return $type === 'percentage' ? ($amount * $value / 100) : $value;
    }

    protected function creditCustomerCashback(VendorOrder $order): void
    {
        $doc = $order->toDocumentArray();
        $cashback = $doc['cashback'] ?? null;

        if (! is_array($cashback)) {
            return;
        }

        $amount = (float) ($cashback['cashbackValue'] ?? 0);
        $customerId = $order->authorID ?? data_get($doc, 'author.id');

        if ($amount <= 0 || ! $customerId) {
            return;
        }

        $this->walletService->topUp($customerId, [
            'amount' => $amount,
            'payment_method' => 'Cashback Amount',
            'payment_status' => 'success',
            'note' => 'Cashback for order #' . $order->id,
            'order_id' => $order->id,
        ]);
    }

    protected function creditWallet(string $userId, float $amount, string $orderId, string $note): void
    {
        AppUser::query()->where('id', $userId)->increment('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => 'order',
            'payment_status' => 'success',
            'note' => $note,
            'order_id' => $orderId,
            'transactionUser' => 'driver',
            'date' => now(),
        ]);
    }

    protected function debitWallet(string $userId, float $amount, string $orderId, string $note): void
    {
        AppUser::query()->where('id', $userId)->decrement('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'amount' => $amount,
            'isTopUp' => false,
            'payment_method' => 'order',
            'payment_status' => 'success',
            'note' => $note,
            'order_id' => $orderId,
            'transactionUser' => 'driver',
            'date' => now(),
        ]);
    }

    protected function resolveWalletUserId(AppUser $driver): string
    {
        if ($driver->ownerId && ! $driver->isOwner) {
            return $driver->ownerId;
        }

        return $driver->id;
    }

    protected function notifyCustomer(Model $order, string $type): void
    {
        $doc = $order->toDocumentArray();
        $fcm = data_get($doc, 'author.fcmToken');

        $this->fcmService->send(
            $fcm,
            'Order update',
            'Your order has been completed',
            ['type' => $type . '_order', 'orderId' => $order->id]
        );
    }
}
