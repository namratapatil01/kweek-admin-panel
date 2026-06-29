<?php

namespace App\Services\Admin;

class AdminModuleRegistry
{
    public function get(string $slug): array
    {
        $slug = str_replace('_', '-', strtolower($slug));
        $modules = config('admin_modules', []);

        if (! isset($modules[$slug])) {
            throw new \InvalidArgumentException("Unknown admin module [{$slug}].");
        }

        $config = $modules[$slug];
        $config['slug'] = $slug;
        $config['route'] = $config['route'] ?? $slug;
        $config['view'] = $config['view'] ?? str_replace('-', '_', $slug);

        return $config;
    }

    public function slugs(): array
    {
        return array_keys(config('admin_modules', []));
    }
}
