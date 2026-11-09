<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Foto operasional untuk Dompdf: baca dari storage, perkecil sebelum base64 agar PDF massal tidak kehabisan memori.
 */
final class OperasionalFotoPdf
{
    /**
     * Satu kali baca + resize → data URI dan ukuran tampilan (untuk hindari decode ganda di view PDF).
     *
     * @return array{src: string, width: int, height: int}|null
     */
    public static function embedPayload(?string $path, int $maxWidth = 115, int $maxHeight = 68): ?array
    {
        $binary = self::readBinary($path);

        if ($binary === null) {
            return null;
        }

        if (! extension_loaded('gd')) {
            return null;
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return null;
        }

        $resized = self::jpegBinaryResized($image, $maxWidth, $maxHeight);

        if ($resized === null) {
            return null;
        }

        return [
            'src' => 'data:image/jpeg;base64,'.base64_encode($resized['binary']),
            'width' => $resized['width'],
            'height' => $resized['height'],
        ];
    }

    public static function embeddedSrc(?string $path, int $maxWidth = 115, int $maxHeight = 68): ?string
    {
        return self::embedPayload($path, $maxWidth, $maxHeight)['src'] ?? null;
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public static function displaySize(?string $path, int $maxWidth = 115, int $maxHeight = 68): ?array
    {
        $payload = self::embedPayload($path, $maxWidth, $maxHeight);

        if ($payload === null) {
            return null;
        }

        return [
            'width' => $payload['width'],
            'height' => $payload['height'],
        ];
    }

    private static function readBinary(?string $path): ?string
    {
        if (! filled($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        try {
            $binary = Storage::disk('public')->get($path);
        } catch (\Throwable) {
            return null;
        }

        if ($binary === '') {
            return null;
        }

        return $binary;
    }

    /**
     * @param  resource|\GdImage  $image
     * @return array{binary: string, width: int, height: int}|null
     */
    private static function jpegBinaryResized($image, int $maxWidth, int $maxHeight): ?array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width < 1 || $height < 1) {
            imagedestroy($image);

            return null;
        }

        $scale = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetW = max(1, (int) round($width * $scale));
        $targetH = max(1, (int) round($height * $scale));

        if ($scale < 1) {
            $resized = imagecreatetruecolor($targetW, $targetH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetW, $targetH, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagejpeg($image, null, 75);
        imagedestroy($image);
        $jpeg = ob_get_clean();

        if ($jpeg === false || $jpeg === '') {
            return null;
        }

        return [
            'binary' => $jpeg,
            'width' => $targetW,
            'height' => $targetH,
        ];
    }
}
