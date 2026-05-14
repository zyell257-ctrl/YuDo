<?php

namespace App\Support;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UploadStorage
{
    private const CLOUDINARY_PREFIX = 'cloudinary:';

    public static function store(UploadedFile $file, string $directory, string $filename): string
    {
        if (self::disk() === 'cloudinary') {
            return self::storeCloudinary($file, $directory, $filename);
        }

        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk(self::disk())->putFileAs($directory, $file, $filename);

        abort_unless(Storage::disk(self::disk())->exists($path), 500, 'File upload gagal disimpan.');

        return $path;
    }

    public static function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (self::isCloudinaryReference($path)) {
            self::cloudinary()->uploadApi()->destroy(self::cloudinaryPublicId($path), [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);

            return;
        }

        Storage::disk(self::disk())->delete($path);
    }

    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (self::isCloudinaryReference($path)) {
            return self::cloudinary()->image(self::cloudinaryAssetId($path))->toUrl();
        }

        return route('media.public', ['path' => ltrim($path, '/')]);
    }

    public static function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (self::isCloudinaryReference($path)) {
            return true;
        }

        return Storage::disk(self::disk())->exists($path);
    }

    public static function disk(): string
    {
        return config('filesystems.uploads_disk', 'public');
    }

    public static function isCloudinaryReference(string $path): bool
    {
        return str_starts_with($path, self::CLOUDINARY_PREFIX);
    }

    private static function storeCloudinary(UploadedFile $file, string $directory, string $filename): string
    {
        $folder = trim(config('services.cloudinary.folder', 'ludo-tracker'), '/');
        $publicId = trim($folder . '/' . trim($directory, '/') . '/' . pathinfo($filename, PATHINFO_FILENAME), '/');

        $result = self::cloudinary()->uploadApi()->upload($file->getRealPath(), [
            'public_id' => $publicId,
            'overwrite' => true,
            'resource_type' => 'image',
        ]);

        $format = $result['format'] ?? strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
        $storedPublicId = $result['public_id'] ?? $publicId;

        return self::CLOUDINARY_PREFIX . $storedPublicId . '.' . $format;
    }

    private static function cloudinary(): Cloudinary
    {
        $url = config('services.cloudinary.url');

        if ($url) {
            return new Cloudinary($url);
        }

        if (!config('services.cloudinary.cloud_name') || !config('services.cloudinary.api_key') || !config('services.cloudinary.api_secret')) {
            throw new RuntimeException('Konfigurasi Cloudinary belum lengkap. Isi CLOUDINARY_URL di Railway Variables.');
        }

        return new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);
    }

    private static function cloudinaryAssetId(string $path): string
    {
        return substr($path, strlen(self::CLOUDINARY_PREFIX));
    }

    private static function cloudinaryPublicId(string $path): string
    {
        $assetId = self::cloudinaryAssetId($path);
        $extension = pathinfo($assetId, PATHINFO_EXTENSION);

        if (!$extension) {
            return $assetId;
        }

        return substr($assetId, 0, -1 * (strlen($extension) + 1));
    }
}
