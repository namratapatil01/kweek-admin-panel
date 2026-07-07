<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\AdminPermissionResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuperAdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $path = resource_path('views/role/save.blade.php');

        if (! is_readable($path)) {
            return;
        }

        $content = file_get_contents($path);
        preg_match_all('/value="([^"]+)"\s+name="([^"]+)\[\]"/', $content, $matches, PREG_SET_ORDER);

        $now = now();

        foreach ($matches as $match) {
            $route = $match[1];
            $permission = $match[2];

            Permission::query()->updateOrCreate(
                ['role_id' => 1, 'routes' => $route],
                [
                    'permission' => $permission,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        // Ensure Super Admin role exists.
        DB::table('role')->updateOrInsert(
            ['id' => 1],
            ['role_name' => 'Super Admin', 'updated_at' => $now, 'created_at' => $now]
        );
    }
}
