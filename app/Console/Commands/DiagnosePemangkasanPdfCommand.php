<?php

namespace App\Console\Commands;

use App\Models\Pemangkasan;
use App\Support\PemangkasanPermohonanPdf;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class DiagnosePemangkasanPdfCommand extends Command
{
    protected $signature = 'pemangkasan:pdf-test {pemangkasan : ID permohonan}';

    protected $description = 'Uji generate PDF gabungan permohonan (untuk diagnosa di server)';

    public function handle(): int
    {
        $pemangkasan = Pemangkasan::query()->find($this->argument('pemangkasan'));

        if (! $pemangkasan) {
            $this->error('Permohonan tidak ditemukan.');

            return self::FAILURE;
        }

        try {
            $response = PemangkasanPermohonanPdf::download($pemangkasan);

            if (! $response instanceof Response) {
                $this->error('Respons bukan HTTP response.');

                return self::FAILURE;
            }

            $bytes = strlen($response->getContent() ?: '');
            $this->info("PDF berhasil dibuat ({$bytes} byte).");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('PDF gagal: '.$exception->getMessage());
            $this->line($exception->getFile().':'.$exception->getLine());

            return self::FAILURE;
        }
    }
}
