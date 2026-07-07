<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SuperAdminPermissionsSeeder::class,
            DefaultSettingsSeeder::class,
            DriverFormDropdownSeeder::class,
            DummyDriversSeeder::class,
            LegacyCollectionSeeder::class,
            KweekBrandingSeeder::class,
            SectionSeeder::class,
            ServiceTypeSeeder::class,
        ]);
    }
}
