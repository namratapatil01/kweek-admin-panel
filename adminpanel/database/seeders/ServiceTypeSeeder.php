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
                'id' => 'service-ondemand',
                'name' => 'On Demand Service',
                'flag' => 'ondemand-service',
            ],
            [
                'id' => 'service-multivendor',
                'name' => 'Multivendor Delivery Service',
                'flag' => 'delivery-service',
            ],
            [
                'id' => 'service-cab',
                'name' => 'Cab Service',
                'flag' => 'cab-service',
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
