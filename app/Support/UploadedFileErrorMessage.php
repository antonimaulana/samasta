<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class UploadedFileErrorMessage
{
    public static function for(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => 'File melebihi batas upload_max_filesize di server PHP. Minta admin naikkan limit (min. 10M) lalu reload PHP-FPM.',
            UPLOAD_ERR_FORM_SIZE => 'File melebihi batas ukuran yang diizinkan form.',
            UPLOAD_ERR_PARTIAL => 'Upload terputus. Coba lagi dengan koneksi stabil atau unggah satu per satu.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang terkirim.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp upload PHP tidak tersedia. Hubungi administrator server.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file sementara. Periksa izin disk.',
            UPLOAD_ERR_EXTENSION => 'Upload diblokir ekstensi PHP.',
            default => 'File gagal diunggah ke server. Periksa ukuran (maks. 10 MB), format JPG/PNG/WebP, post_max_size, dan client_max_body_size Nginx.',
        };
    }

    public static function postTooLargeHint(): string
    {
        $postMax = ini_get('post_max_size') ?: '?';
        $uploadMax = ini_get('upload_max_filesize') ?: '?';

        return 'Total unggahan melebihi post_max_size PHP ('.$postMax.') atau batas Nginx. Kurangi jumlah/ukuran foto sekaligus, atau minta admin set upload_max_filesize & post_max_size min. 20M dan client_max_body_size 20M.';
    }

    public static function postMaxBytes(): int
    {
        return self::iniSizeToBytes(ini_get('post_max_size') ?: '8M');
    }

    public static function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
