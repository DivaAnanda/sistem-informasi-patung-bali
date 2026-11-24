<?php

namespace App\Services\Photos;

use GdImage;

/**
 * Turns an upload into the two JPEGs the site serves. Re-encoding through GD is what removes
 * EXIF (including GPS); the orientation tag is applied to the pixels first so nothing ends up sideways.
 */
final class PhotoProcessor
{
    public const FULL_SIZE = 1280;

    public const THUMB_SIZE = 640;

    private const FULL_QUALITY = 85;

    private const THUMB_QUALITY = 72;

    private const MIN_SIDE = 300;

    private const MAX_PIXELS = 50_000_000;

    /**
     * @return array{full: string, thumb: string}
     */
    public function process(string $path): array
    {
        $image = $this->load($path);

        try {
            return [
                'full' => $this->encode($this->fit($image, self::FULL_SIZE), self::FULL_QUALITY),
                'thumb' => $this->encode($this->fitWidth($image, self::THUMB_SIZE), self::THUMB_QUALITY),
            ];
        } finally {
            imagedestroy($image);
        }
    }

    private function load(string $path): GdImage
    {
        $info = @getimagesize($path);
        $types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

        if ($info === false || ! in_array($info[2], $types, true)) {
            throw new InvalidPhoto(__('Format foto harus JPG, PNG, atau WebP. Foto HEIC dari iPhone perlu diubah ke JPG dulu.'));
        }

        [$width, $height] = $info;

        if (min($width, $height) < self::MIN_SIDE) {
            throw new InvalidPhoto(__('Foto terlalu kecil. Sisi terpendek minimal :min piksel.', ['min' => self::MIN_SIDE]));
        }

        if ($width * $height > self::MAX_PIXELS) {
            throw new InvalidPhoto(__('Resolusi foto terlalu besar untuk diproses.'));
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        };

        if ($image === false) {
            throw new InvalidPhoto(__('Foto rusak atau tidak bisa dibaca.'));
        }

        return $info[2] === IMAGETYPE_JPEG ? $this->orient($image, $path) : $image;
    }

    private function orient(GdImage $image, string $path): GdImage
    {
        $orientation = function_exists('exif_read_data') ? (@exif_read_data($path)['Orientation'] ?? 1) : 1;

        $rotated = match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated === null || $rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function fit(GdImage $image, int $longest): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $longest / max($width, $height));

        return $this->resize($image, (int) round($width * $scale), (int) round($height * $scale));
    }

    private function fitWidth(GdImage $image, int $maxWidth): GdImage
    {
        $width = imagesx($image);
        $scale = min(1, $maxWidth / $width);

        return $this->resize($image, (int) round($width * $scale), (int) round(imagesy($image) * $scale));
    }

    private function resize(GdImage $image, int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        // PNG/WebP transparency becomes white instead of black once flattened to JPEG.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $canvas;
    }

    private function encode(GdImage $image, int $quality): string
    {
        imageinterlace($image, true);
        ob_start();
        imagejpeg($image, null, $quality);
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
