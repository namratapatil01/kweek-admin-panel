<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'id' => 'nwjicjbMYwb5hPoEitAS',
                'name' => 'On Demand Service',
                'flag' => 'ondemand-service',
            ],
            [
                'id' => 'TGTP44PgU5G6BU2uP7iY',
                'name' => 'Multivendor Delivery Service',
                'flag' => 'delivery-service',
            ],
            [
                'id' => 'ny3sssVJ7FCrPgxvsZNO',
                'name' => 'Ecommerce Service',
                'flag' => 'ecommerce-service',
            ],
            [
                'id' => 'zxzjypGIugTmlb0ZeOT0',
                'name' => 'Cab Service',
                'flag' => 'cab-service',
            ],
            [
                'id' => 'sDsB9pMGXLBMnbQiTMKF',
                'name' => 'Parcel Delivery Service',
                'flag' => 'parcel_delivery',
            ],
            [
                'id' => 'FDOAplq4EHOQ3U5SLsRr',
                'name' => 'Rental Service',
                'flag' => 'rental-service',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['id' => $service['id']],
                array_merge($service, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
