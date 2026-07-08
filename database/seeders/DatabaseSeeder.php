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
            // One-time fs_* → MySQL migration. Run manually: php artisan db:seed --class=LegacyCollectionSeeder
            KweekBrandingSeeder::class,
            SectionSeeder::class,
            ServiceTypeSeeder::class,
        ]);
    }
}
