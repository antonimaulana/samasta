<?php

namespace App\Support;

use App\Models\Taman;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RthArQrGenerator
{
    public function scanUrl(Taman|int $taman): string
    {
        $key = $taman instanceof Taman ? $taman->getKey() : $taman;

        return route('rth.ar-scan', ['taman' => $key]);
    }

    public function svg(string $url, int $size = 480): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(2)
            ->color(5, 150, 105)
            ->backgroundColor(255, 255, 255)
            ->generate($url);
    }

    public function png(string $url, int $size = 480): string
    {
        if (! extension_loaded('imagick')) {
            throw new \RuntimeException(
                'Ekstensi PHP imagick diperlukan untuk QR PNG. Gunakan endpoint SVG atau pasang imagick.'
            );
        }

        return QrCode::format('png')
            ->size($size)
            ->margin(2)
            ->color(5, 150, 105)
            ->backgroundColor(255, 255, 255)
            ->generate($url);
    }

    public function preferredFormat(): string
    {
        return extension_loaded('imagick') ? 'png' : 'svg';
    }
}
