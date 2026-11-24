<?php

namespace App\Support;

use RuntimeException;

final class StatuePhotos
{
    private const THUMB_WIDTH = 640;

    private const THUMB_QUALITY = 72;

    public static function directory(): string
    {
        return public_path('images/patung');
    }

    public static function thumbnailDirectory(): string
    {
        return self::directory().'/thumbs';
    }

    public static function thumbnailPath(string $file): string
    {
        return self::thumbnailDirectory().'/'.$file;
    }

    /**
     * Photos added through the admin panel live in Supabase Storage under this prefix; plain file
     * names are the original photos shipped in public/images/patung.
     */
    public const UPLOAD_PREFIX = 'uploads/';

    public static function isUpload(string $file): bool
    {
        return str_starts_with($file, self::UPLOAD_PREFIX);
    }

    public static function thumbnailKey(string $key): string
    {
        return dirname($key).'/thumbs/'.basename($key);
    }

    public static function url(string $file): string
    {
        return self::isUpload($file) ? self::storageUrl($file) : asset('images/patung/'.$file);
    }

    /**
     * Falls back to the full photo when no thumbnail has been generated yet.
     */
    public static function thumbnailUrl(string $file): string
    {
        if (self::isUpload($file)) {
            return self::storageUrl(self::thumbnailKey($file));
        }

        return is_file(self::thumbnailPath($file)) ? asset('images/patung/thumbs/'.$file) : self::url($file);
    }

    private static function storageUrl(string $key): string
    {
        return rtrim((string) config('patung.photo_storage.public_url'), '/').'/'.$key;
    }

    public static function makeThumbnail(string $source, string $target): void
    {
        $image = @imagecreatefromjpeg($source);

        if ($image === false) {
            throw new RuntimeException("Foto {$source} tidak bisa dibaca.");
        }

        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        $thumbnail = imagescale($image, min(self::THUMB_WIDTH, imagesx($image)));
        imageinterlace($thumbnail, true);
        imagejpeg($thumbnail, $target, self::THUMB_QUALITY);
    }
}
