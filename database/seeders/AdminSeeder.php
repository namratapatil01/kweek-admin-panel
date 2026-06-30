<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->updateOrInsert(
            ['id' => 1],
            [
                'role_name' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@emart.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role_id' => 1,
            ]
        );
    }
}
