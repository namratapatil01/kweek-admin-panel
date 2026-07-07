<?php

namespace App\Support;

use App\Models\Permission;

class AdminPermissionResolver
{
    /**
     * Resolve permission module keys for the authenticated admin user.
     *
     * @return array<int, string>
     */
    public static function modulesForUser(?object $user): array
    {
        if (! $user) {
            return [];
        }

        $roleId = (int) ($user->role_id ?? 0);
        $permissions = Permission::query()
            ->where('role_id', $roleId)
            ->pluck('permission')
            ->unique()
            ->values()
            ->all();

        if ($roleId === 1) {
            $permissions = array_values(array_unique(array_merge(
                $permissions,
                config('admin_permissions.modules', [])
            )));
        }

        return $permissions;
    }

    /**
     * Resolve route slugs stored in the permissions table (for session middleware).
     *
     * @return array<int, string>
     */
    public static function routesForUser(?object $user): array
    {
        if (! $user) {
            return [];
        }

        $roleId = (int) ($user->role_id ?? 0);
        $routes = Permission::query()
            ->where('role_id', $roleId)
            ->pluck('routes')
            ->unique()
            ->values()
            ->all();

        if ($roleId === 1 && empty($routes)) {
            $routes = self::allRoutesFromRoleForm();
        }

        return $routes;
    }

    /**
     * @return array<int, string>
     */
    public static function allRoutesFromRoleForm(): array
    {
        static $routes = null;

        if ($routes !== null) {
            return $routes;
        }

        $path = resource_path('views/role/save.blade.php');
        if (! is_readable($path)) {
            return $routes = [];
        }

        $content = file_get_contents($path);
        preg_match_all('/value="([^"]+)"\s+name="([^"]+)\[\]"/', $content, $matches);

        $routes = array_values(array_unique($matches[1] ?? []));

        return $routes;
    }
}
