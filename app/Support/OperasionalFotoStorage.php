<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OperasionalFotoStorage
{
    public const PEMELIHARAAN_DIRECTORY = 'pemeliharaan-taman';

    /**
     * @throws ValidationException
     */
    public static function storePemeliharaan(UploadedFile $file, string $fieldKey): string
    {
        return self::storeOnPublicDisk(self::PEMELIHARAAN_DIRECTORY, $file, $fieldKey);
    }

    /**
     * @throws ValidationException
     */
    public static function storeOnPublicDisk(string $directory, UploadedFile $file, string $fieldKey): string
    {
        $label = strtolower(PemeliharaanTaman::FOTO_FIELDS[$fieldKey] ?? $fieldKey);

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                $fieldKey => self::invalidUploadMessage($file, $label),
            ]);
        }

        $disk = Storage::disk('public');

        try {
            $disk->makeDirectory($directory);
        } catch (\Throwable $exception) {
            Log::error('Operasional foto: gagal membuat folder penyimpanan.', [
                'directory' => $directory,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            $path = $file->store($directory, 'public');
        } catch (\Throwable $exception) {
            Log::error('Operasional foto: gagal menyimpan file.', [
                'directory' => $directory,
                'field' => $fieldKey,
                'message' => $exception->getMessage(),
            ]);
            $path = false;
        }

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                $fieldKey => self::storageFailureMessage($disk, $directory),
            ]);
        }

        if (! $disk->exists($path) && ! is_file($disk->path($path))) {
            Log::error('Operasional foto: file tidak ditemukan setelah unggah.', [
                'path' => $path,
                'directory' => $directory,
                'field' => $fieldKey,
            ]);

            throw ValidationException::withMessages([
                $fieldKey => self::storageFailureMessage($disk, $directory),
            ]);
        }

        return $path;
    }

    private static function invalidUploadMessage(UploadedFile $file, string $label): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Ukuran {$label} melebihi batas server. Kompres foto atau minta admin menaikkan limit upload (post_max_size / client_max_body_size).",
            UPLOAD_ERR_PARTIAL => "Unggah {$label} terputus. Coba lagi dengan koneksi stabil.",
            UPLOAD_ERR_NO_FILE => "{$label} wajib diunggah.",
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => "Server tidak dapat menerima file {$label}. Hubungi administrator (folder tmp/storage).",
            default => "File {$label} tidak valid. Unggah ulang.",
        };
    }

    private static function storageFailureMessage($disk, string $directory): string
    {
        $root = method_exists($disk, 'path') ? $disk->path('') : storage_path('app/public');
        $target = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$directory;

        if (! is_dir($target) || ! is_writable($target)) {
            return 'Foto gagal disimpan ke server. Periksa izin tulis folder storage/app/public/'.$directory.' (www-data) dan jalankan php artisan storage:link.';
        }

        return 'Foto gagal disimpan ke server. Periksa ruang disk, izin folder storage, atau hubungi administrator.';
    }
}
