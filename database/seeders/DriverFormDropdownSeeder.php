<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DriverFormDropdownSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Zones
        $zones = [
            ['id' => Str::uuid()->toString(), 'name' => 'Mumbai City', 'publish' => 1],
            ['id' => Str::uuid()->toString(), 'name' => 'Delhi NCR', 'publish' => 1],
            ['id' => Str::uuid()->toString(), 'name' => 'Bangalore Central', 'publish' => 1],
            ['id' => Str::uuid()->toString(), 'name' => 'Pune West', 'publish' => 1],
        ];
        DB::table('zones')->insert($zones);

        // 2. Seed Car Makes
        $makes = [
            ['id' => Str::uuid()->toString(), 'name' => 'Maruti Suzuki'],
            ['id' => Str::uuid()->toString(), 'name' => 'Hyundai'],
            ['id' => Str::uuid()->toString(), 'name' => 'Tata Motors'],
            ['id' => Str::uuid()->toString(), 'name' => 'Mahindra'],
            ['id' => Str::uuid()->toString(), 'name' => 'Honda'],
            ['id' => Str::uuid()->toString(), 'name' => 'Toyota'],
        ];
        DB::table('car_makes')->insert($makes);

        // 3. Seed Vehicle Types for Cab and Rental
        // We will just put them broadly for any section for now, or without section logic just to show them.
        $cabSections = DB::table('sections')->where('serviceTypeFlag', 'cab-service')->pluck('id')->toArray();
        $rentalSections = DB::table('sections')->where('serviceTypeFlag', 'rental-service')->pluck('id')->toArray();

        $secId = !empty($cabSections) ? $cabSections[0] : null;
        $rentSecId = !empty($rentalSections) ? $rentalSections[0] : null;

        $vTypes = [
            ['id' => Str::uuid()->toString(), 'name' => 'Hatchback', 'sectionId' => $secId],
            ['id' => Str::uuid()->toString(), 'name' => 'Sedan', 'sectionId' => $secId],
            ['id' => Str::uuid()->toString(), 'name' => 'SUV', 'sectionId' => $secId],
            ['id' => Str::uuid()->toString(), 'name' => 'Luxury', 'sectionId' => $secId],
        ];
        DB::table('vehicle_types')->insert($vTypes);

        $rTypes = [
            ['id' => Str::uuid()->toString(), 'name' => 'Mini', 'sectionId' => $rentSecId],
            ['id' => Str::uuid()->toString(), 'name' => 'Sedan', 'sectionId' => $rentSecId],
            ['id' => Str::uuid()->toString(), 'name' => 'SUV 7-Seater', 'sectionId' => $rentSecId],
        ];
        DB::table('rental_vehicle_types')->insert($rTypes);
    }
}
