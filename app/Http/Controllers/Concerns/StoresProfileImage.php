<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Str;

trait StoresProfileImage
{
    protected function storeProfileImage(?string $imageData): ?string
    {
        if (!$imageData) {
            return null;
        }

        if (!str_starts_with($imageData, 'data:image')) {
            return $imageData;
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode(substr($imageData, strpos($imageData, ',') + 1), true);
        if ($binary === false) {
            return null;
        }

        $filename = (string) Str::uuid() . '.' . $extension;
        $relative = 'images/' . $filename;
        $absolute = public_path('storage/' . $relative);
        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0755, true);
        }
        file_put_contents($absolute, $binary);

        return '/storage/' . $relative;
    }
}
