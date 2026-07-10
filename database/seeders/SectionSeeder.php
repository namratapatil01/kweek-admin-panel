<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Ensure core sections exist, including the Shop (ecommerce) module.
     */
    public function run(): void
    {
        $shopSection = [
            'id'              => '6285dd3281531',
            'name'            => 'Shop',
            'serviceType'     => 'Ecommerce Service',
            'serviceTypeFlag' => 'ecommerce-service',
            'isActive'        => 1,
            'sectionImage'    => 'https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/images%2Ffashion_1726734056377.png?alt=media&token=2847d7ab-1aa0-47d4-afaa-b230838bdff4',
            'color'           => '#6045c8',
            'nearByRadius'    => 13000,
            'delivery_charge' => 12,
            'referralAmount'  => 20,
            'dine_in_active'  => 0,
            'is_product_details' => 0,
            'enableCashbackOffer' => 0,
            'payload'         => json_encode([
                'adminCommision' => [
                    'enable'     => true,
                    'commission' => 10,
                    'type'       => 'percentage',
                ],
            ]),
            'updated_at'      => now(),
        ];

        $exists = DB::table('sections')->where('id', $shopSection['id'])->exists();

        if ($exists) {
            DB::table('sections')->where('id', $shopSection['id'])->update($shopSection);
        } else {
            DB::table('sections')->insert(array_merge($shopSection, [
                'created_at' => now(),
            ]));
        }
    }
}
