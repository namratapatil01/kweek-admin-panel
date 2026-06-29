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

        $disk = config('filesystems.default', 'local');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()), [
            'visibility' => $visibility,
        ]);

        return [
            'path' => $path,
            'url' => $visibility === 'public'
                ? Storage::disk($disk)->url($path)
                : null,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default', 'local');

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
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf', 'video/mp4',
        ]);

        if ($file->getSize() > $maxKb * 1024) {
            throw new \InvalidArgumentException('File exceeds maximum allowed size.');
        }

        if (! in_array($file->getMimeType(), $allowed, true)) {
            throw new \InvalidArgumentException('File type is not allowed.');
        }
    }
}
