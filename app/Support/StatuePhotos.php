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

    public static function url(string $file): string
    {
        return asset('images/patung/'.$file);
    }

    /**
     * Falls back to the full photo when no thumbnail has been generated yet.
     */
    public static function thumbnailUrl(string $file): string
    {
        return is_file(self::thumbnailPath($file)) ? asset('images/patung/thumbs/'.$file) : self::url($file);
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
