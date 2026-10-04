<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TamanGalleryImageNormalizer
{
    /**
     * Center-crop to 16:9, resize to canvas 1920×1080, save as JPEG.
     */
    public function normalizeAndStore(UploadedFile $file, string $directory = 'taman_galeri'): string
    {
        if (! extension_loaded('gd')) {
            throw new \InvalidArgumentException(
                'Ekstensi PHP GD belum aktif di server. Hubungi administrator untuk mengaktifkan extension=gd.'
            );
        }

        $source = $this->loadImage($file);
        if ($source === null) {
            throw new \InvalidArgumentException(
                'Format foto tidak didukung atau file rusak. Gunakan JPG, PNG, atau WebP (maks. '.(Taman::GALLERY_MAX_UPLOAD_KILOBYTES / 1024).' MB).'
            );
        }

        $source = $this->applyExifOrientation($file, $source);

        [$cropX, $cropY, $cropW, $cropH] = self::coverCropRect(
            imagesx($source),
            imagesy($source),
        );

        $canvasW = Taman::GALLERY_RECOMMENDED_WIDTH;
        $canvasH = Taman::GALLERY_RECOMMENDED_HEIGHT;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        if ($canvas === false) {
            imagedestroy($source);
            throw new \RuntimeException('Gagal menyiapkan canvas foto.');
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $cropX,
            $cropY,
            $canvasW,
            $canvasH,
            $cropW,
            $cropH,
        );

        imagedestroy($source);

        ob_start();
        imagejpeg($canvas, null, 88);
        $binary = ob_get_clean();
        imagedestroy($canvas);

        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Gagal menyimpan foto hasil normalisasi.');
        }

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.jpg';
        if (! \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary)) {
            throw new \RuntimeException('Gagal menulis file foto ke storage. Pastikan php artisan storage:link sudah dijalankan.');
        }

        return $path;
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int} crop x, y, width, height on source
     */
    public static function coverCropRect(int $sourceWidth, int $sourceHeight): array
    {
        if ($sourceWidth < 1 || $sourceHeight < 1) {
            return [0, 0, max(1, $sourceWidth), max(1, $sourceHeight)];
        }

        $targetRatio = Taman::GALLERY_ASPECT_WIDTH / Taman::GALLERY_ASPECT_HEIGHT;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropH = $sourceHeight;
            $cropW = (int) round($sourceHeight * $targetRatio);

            return [
                (int) max(0, round(($sourceWidth - $cropW) / 2)),
                0,
                min($cropW, $sourceWidth),
                $cropH,
            ];
        }

        $cropW = $sourceWidth;
        $cropH = (int) round($sourceWidth / $targetRatio);

        return [
            0,
            (int) max(0, round(($sourceHeight - $cropH) / 2)),
            $cropW,
            min($cropH, $sourceHeight),
        ];
    }

    /**
     * @return \GdImage|null
     */
    private function loadImage(UploadedFile $file): ?\GdImage
    {
        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            return null;
        }

        $mime = strtolower($file->getMimeType() ?? '');
        $extension = strtolower($file->getClientOriginalExtension());

        $fromMime = match (true) {
            str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => @imagecreatefromjpeg($path) ?: null,
            str_contains($mime, 'png') => @imagecreatefrompng($path) ?: null,
            str_contains($mime, 'webp') => function_exists('imagecreatefromwebp')
                ? (@imagecreatefromwebp($path) ?: null)
                : null,
            default => null,
        };

        if ($fromMime instanceof \GdImage) {
            return $fromMime;
        }

        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path) ?: null,
            'png' => @imagecreatefrompng($path) ?: null,
            'webp' => function_exists('imagecreatefromwebp')
                ? (@imagecreatefromwebp($path) ?: null)
                : null,
            default => null,
        };
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function applyExifOrientation(UploadedFile $file, \GdImage $image): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $path = $file->getRealPath();
        if ($path === false) {
            return $image;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($path);
        if (! is_array($exif) || empty($exif['Orientation'])) {
            return $image;
        }

        $angle = match ((int) $exif['Orientation']) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
