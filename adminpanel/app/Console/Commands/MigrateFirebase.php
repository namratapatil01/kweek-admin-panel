<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\AppUser;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\ProviderOrder;
use App\Models\Ride;

class MigrateFirebase extends Command
{
    protected $signature = 'nexa:migrate-firebase';
    protected $description = 'Import Firestore collections from collections.json to MySQL';

    public function handle()
    {
        $filePath = base_path('collections.json');
        if (!file_exists($filePath)) {
            $this->error("collections.json not found in root path: {$filePath}");
            return 1;
        }

        $this->info("Loading collections.json...");
        $data = json_decode(file_get_contents($filePath), true);

        if (!isset($data['__collections__'])) {
            $this->error("Invalid collections.json format: __collections__ key missing.");
            return 1;
        }

        $collections = $data['__collections__'];

        // 1. Migrate Sections
        $this->migrateSections($collections['sections'] ?? []);

        // 2. Migrate Settings
        $this->migrateSettings($collections['settings'] ?? []);

        // 3. Migrate Currencies
        $this->migrateCurrencies($collections['currencies'] ?? []);

        // 4. Migrate Users
        $this->migrateUsers($collections['users'] ?? []);

        // 5. Migrate Vendors
        $this->migrateVendors($collections['vendors'] ?? []);

        // 6. Migrate Vendor Orders
        $this->migrateVendorOrders($collections['vendor_orders'] ?? []);

        // 7. Migrate Parcel Orders
        $this->migrateParcelOrders($collections['parcel_orders'] ?? []);

        // 8. Migrate Rental Orders
        $this->migrateRentalOrders($collections['rental_orders'] ?? []);

        // 9. Migrate Provider Orders
        $this->migrateProviderOrders($collections['provider_orders'] ?? []);

        // 10. Migrate Rides (Cabs)
        $this->migrateRides($collections['rides'] ?? []);

        $this->info("All Dashboard module data successfully migrated!");
        return 0;
    }

    private function parseTimestamp($val)
    {
        if ($val === null || $val === '') return null;
        if (is_array($val)) {
            if (isset($val['__datatype__']) && $val['__datatype__'] === 'timestamp') {
                return date('Y-m-d H:i:s', $val['value']['_seconds'] ?? time());
            }
            if (isset($val['_seconds'])) {
                return date('Y-m-d H:i:s', $val['_seconds']);
            }
        }
        if (is_string($val)) {
            $timestamp = strtotime($val);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
        if (is_numeric($val)) {
            return date('Y-m-d H:i:s', (int)$val);
        }
        return null;
    }

    private function intOrNull($val)
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (int)$val : null;
    }

    private function floatOrNull($val)
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (double)$val : null;
    }

    private function migrateSections($docs)
    {
        $this->info("Migrating Sections...");
        Section::truncate();
        foreach ($docs as $id => $doc) {
            Section::create([
                'id' => $doc['id'] ?? $id,
                'name' => $doc['name'] ?? null,
                'serviceType' => $doc['serviceType'] ?? null,
                'serviceTypeFlag' => $doc['serviceTypeFlag'] ?? null,
                'isActive' => $doc['isActive'] ?? true,
                'sectionImage' => $doc['sectionImage'] ?? null,
                'color' => $doc['color'] ?? null,
                'nearByRadius' => $this->intOrNull($doc['nearByRadius'] ?? null),
                'delivery_charge' => $this->intOrNull($doc['delivery_charge'] ?? null),
                'adminCommision' => $doc['adminCommision'] ?? null,
                'dine_in_active' => $doc['dine_in_active'] ?? false,
                'rideType' => $doc['rideType'] ?? null,
                'is_product_details' => $doc['is_product_details'] ?? false,
                'cab_service_template' => $doc['cab_service_template'] ?? null,
                'enableCashbackOffer' => $doc['enableCashbackOffer'] ?? false,
                'theme' => $doc['theme'] ?? null,
                'referralAmount' => $this->intOrNull($doc['referralAmount'] ?? null),
            ]);
        }
    }

    private function migrateSettings($docs)
    {
        $this->info("Migrating Settings...");
        Setting::truncate();
        foreach ($docs as $id => $doc) {
            unset($doc['__collections__']);
            Setting::create([
                'id' => $id,
                'value' => $doc,
            ]);
        }
    }

    private function migrateCurrencies($docs)
    {
        $this->info("Migrating Currencies...");
        Currency::truncate();
        foreach ($docs as $id => $doc) {
            Currency::create([
                'id' => $doc['id'] ?? $id,
                'country' => $doc['country'] ?? null,
                'symbol' => $doc['symbol'] ?? null,
                'code' => $doc['code'] ?? null,
                'isActive' => $doc['isActive'] ?? false,
                'symbolAtRight' => $doc['symbolAtRight'] ?? false,
                'decimal_degits' => $this->intOrNull($doc['decimal_degits'] ?? 0),
            ]);
        }
    }

    private function migrateUsers($docs)
    {
        $this->info("Migrating Users...");
        AppUser::truncate();
        foreach ($docs as $id => $doc) {
            AppUser::create([
                'id' => $doc['id'] ?? $id,
                'firstName' => $doc['firstName'] ?? null,
                'lastName' => $doc['lastName'] ?? null,
                'email' => $doc['email'] ?? null,
                'phoneNumber' => $doc['phoneNumber'] ?? null,
                'role' => $doc['role'] ?? null,
                'active' => $doc['active'] ?? true,
                'isActive' => $doc['isActive'] ?? $doc['active'] ?? true,
                'isOwner' => $doc['isOwner'] ?? false,
                'isDocumentVerify' => $doc['isDocumentVerify'] ?? false,
                'sectionId' => $doc['sectionId'] ?? $doc['section_id'] ?? null,
                'section_id' => $doc['section_id'] ?? $doc['sectionId'] ?? null,
                'vendorID' => $doc['vendorID'] ?? null,
                'rideType' => $doc['rideType'] ?? null,
                'serviceType' => $doc['serviceType'] ?? null,
                'profilePictureURL' => $doc['profilePictureURL'] ?? null,
                'wallet_amount' => $this->floatOrNull($doc['wallet_amount'] ?? 0.0),
                'orderCompleted' => $this->intOrNull($doc['orderCompleted'] ?? 0),
                'fcmToken' => $doc['fcmToken'] ?? null,
                'settings' => $doc['settings'] ?? null,
                'userBankDetails' => $doc['userBankDetails'] ?? null,
                'shippingAddress' => $doc['shippingAddress'] ?? null,
                'location' => $doc['location'] ?? null,
                'carName' => $doc['carName'] ?? null,
                'carNumber' => $doc['carNumber'] ?? null,
                'carColor' => $doc['carColor'] ?? null,
                'vehicleType' => $doc['vehicleType'] ?? null,
                'carPictureURL' => $doc['carPictureURL'] ?? null,
                'driverRate' => $doc['driverRate'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateVendors($docs)
    {
        $this->info("Migrating Vendors...");
        Vendor::truncate();
        foreach ($docs as $id => $doc) {
            Vendor::create([
                'id' => $doc['id'] ?? $id,
                'title' => $doc['title'] ?? null,
                'description' => $doc['description'] ?? null,
                'photo' => $doc['photo'] ?? null,
                'categoryPhoto' => $doc['categoryPhoto'] ?? null,
                'latitude' => $this->floatOrNull($doc['latitude'] ?? null),
                'longitude' => $this->floatOrNull($doc['longitude'] ?? null),
                'section_id' => $doc['section_id'] ?? null,
                'reviewsSum' => $this->floatOrNull($doc['reviewsSum'] ?? 0.0),
                'reviewsCount' => $this->intOrNull($doc['reviewsCount'] ?? 0),
                'reststatus' => $doc['reststatus'] ?? true,
                'walletAmount' => $this->floatOrNull($doc['walletAmount'] ?? 0.0),
                'phonenumber' => $doc['phonenumber'] ?? null,
                'opentime' => $doc['opentime'] ?? $doc['openDineTime'] ?? null,
                'closetime' => $doc['closetime'] ?? $doc['closeDineTime'] ?? null,
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'location' => $doc['location'] ?? null,
                'photos' => $doc['photos'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateVendorOrders($docs)
    {
        $this->info("Migrating Vendor Orders...");
        VendorOrder::truncate();
        foreach ($docs as $id => $doc) {
            VendorOrder::create([
                'id' => $doc['id'] ?? $id,
                'status' => $doc['status'] ?? null,
                'section_id' => $doc['section_id'] ?? null,
                'vendorID' => $doc['vendorID'] ?? null,
                'authorID' => $doc['authorID'] ?? null,
                'driverID' => $doc['driverID'] ?? null,
                'deliveryCharge' => $this->floatOrNull($doc['deliveryCharge'] ?? null),
                'discount' => $this->floatOrNull($doc['discount'] ?? null),
                'tip_amount' => $this->floatOrNull($doc['tip_amount'] ?? null),
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'adminCommissionType' => $doc['adminCommissionType'] ?? null,
                'payment_method' => $doc['payment_method'] ?? null,
                'products' => $doc['products'] ?? null,
                'taxSetting' => $doc['taxSetting'] ?? null,
                'address' => $doc['address'] ?? null,
                'takeAway' => $doc['takeAway'] ?? false,
                'vendor' => $doc['vendor'] ?? null,
                'author' => $doc['author'] ?? null,
                'driver' => $doc['driver'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateParcelOrders($docs)
    {
        $this->info("Migrating Parcel Orders...");
        ParcelOrder::truncate();
        foreach ($docs as $id => $doc) {
            ParcelOrder::create([
                'id' => $doc['id'] ?? $id,
                'status' => $doc['status'] ?? null,
                'sectionId' => $doc['sectionId'] ?? null,
                'section_id' => $doc['section_id'] ?? null,
                'authorID' => $doc['authorID'] ?? null,
                'driverID' => $doc['driverID'] ?? null,
                'subTotal' => $this->floatOrNull($doc['subTotal'] ?? null),
                'discount' => $this->floatOrNull($doc['discount'] ?? null),
                'payment_method' => $doc['payment_method'] ?? null,
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'adminCommissionType' => $doc['adminCommissionType'] ?? null,
                'receiver' => $doc['receiver'] ?? null,
                'sender' => $doc['sender'] ?? null,
                'taxSetting' => $doc['taxSetting'] ?? null,
                'author' => $doc['author'] ?? null,
                'driver' => $doc['driver'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateRentalOrders($docs)
    {
        $this->info("Migrating Rental Orders...");
        RentalOrder::truncate();
        foreach ($docs as $id => $doc) {
            RentalOrder::create([
                'id' => $doc['id'] ?? $id,
                'status' => $doc['status'] ?? null,
                'sectionId' => $doc['sectionId'] ?? null,
                'section_id' => $doc['section_id'] ?? null,
                'authorID' => $doc['authorID'] ?? null,
                'driverId' => $doc['driverId'] ?? null,
                'subTotal' => $this->floatOrNull($doc['subTotal'] ?? null),
                'discount' => $this->floatOrNull($doc['discount'] ?? null),
                'tip_amount' => $this->floatOrNull($doc['tip_amount'] ?? null),
                'paymentMethod' => $doc['paymentMethod'] ?? null,
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'adminCommissionType' => $doc['adminCommissionType'] ?? null,
                'taxSetting' => $doc['taxSetting'] ?? null,
                'author' => $doc['author'] ?? null,
                'driver' => $doc['driver'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateProviderOrders($docs)
    {
        $this->info("Migrating Provider Orders...");
        ProviderOrder::truncate();
        foreach ($docs as $id => $doc) {
            ProviderOrder::create([
                'id' => $doc['id'] ?? $id,
                'status' => $doc['status'] ?? null,
                'sectionId' => $doc['sectionId'] ?? null,
                'section_id' => $doc['section_id'] ?? null,
                'authorID' => $doc['authorID'] ?? null,
                'discount' => $this->floatOrNull($doc['discount'] ?? null),
                'payment_method' => $doc['payment_method'] ?? null,
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'adminCommissionType' => $doc['adminCommissionType'] ?? null,
                'taxSetting' => $doc['taxSetting'] ?? null,
                'author' => $doc['author'] ?? null,
                'provider' => $doc['provider'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }

    private function migrateRides($docs)
    {
        $this->info("Migrating Rides...");
        Ride::truncate();
        foreach ($docs as $id => $doc) {
            Ride::create([
                'id' => $doc['id'] ?? $id,
                'status' => $doc['status'] ?? null,
                'sectionId' => $doc['sectionId'] ?? null,
                'section_id' => $doc['section_id'] ?? null,
                'authorID' => $doc['authorID'] ?? null,
                'driverId' => $doc['driverId'] ?? null,
                'subTotal' => $this->floatOrNull($doc['subTotal'] ?? null),
                'discount' => $this->floatOrNull($doc['discount'] ?? null),
                'tip_amount' => $this->floatOrNull($doc['tip_amount'] ?? null),
                'paymentMethod' => $doc['paymentMethod'] ?? null,
                'adminCommission' => $this->floatOrNull($doc['adminCommission'] ?? null),
                'adminCommissionType' => $doc['adminCommissionType'] ?? null,
                'taxSetting' => $doc['taxSetting'] ?? null,
                'author' => $doc['author'] ?? null,
                'driver' => $doc['driver'] ?? null,
                'createdAt' => $this->parseTimestamp($doc['createdAt'] ?? null),
            ]);
        }
    }
}
