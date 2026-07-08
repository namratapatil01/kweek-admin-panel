<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    public function upload(UploadedFile $file, string $directory = 'uploads', string $visibility = 'public'): array
    {
        $this->validateFile($file);

        $disk = $visibility === 'public' ? 'public' : config('filesystems.default', 'local');
        $filename = Str::uuid() . '.' . $this->extensionFor($file);
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()), [
            'visibility' => $visibility,
        ]);

        return [
            'path' => $path,
            'url' => $visibility === 'public'
                ? '/storage/' . ltrim($path, '/')
                : null,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        if ($disk === null) {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            $disk = config('filesystems.default', 'local');
        }

        return Storage::disk($disk)->delete($path);
    }

    public function temporaryUrl(string $path, int $minutes = 30, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default', 'local');

        return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes($minutes));
    }

    protected function validateFile(UploadedFile $file): void
    {
        $maxKb = (int) config('kweek.upload.max_size_kb', 10240);
        $allowed = config('kweek.upload.allowed_mimes', [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/svg+xml',
            'application/pdf', 'video/mp4',
        ]);

        if ($file->getSize() > $maxKb * 1024) {
            throw new \InvalidArgumentException('File exceeds maximum allowed size.');
        }

        if (! in_array($file->getMimeType(), $allowed, true)) {
            throw new \InvalidArgumentException('File type is not allowed.');
        }
    }

    protected function extensionFor(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $known = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'pdf', 'mp4'];

        if (in_array($extension, $known, true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            default => 'jpg',
        };
    }
}
