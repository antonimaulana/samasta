<?php

namespace App\Support;

use App\Models\DpaPaketPekerjaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DpaDocumentGenerator
{
    /**
     * @param  array<string, mixed>  $inputData
     */
    public function generate(DpaPaketPekerjaan $paket, string $tahap, string $kode, array $inputData): string
    {
        PdfExport::ensureGdLoaded();

        $paket->loadMissing(['dpa.tahunAnggaran', 'penyedia']);

        $html = view($this->viewForKode($kode), $this->viewData($paket, $kode, $inputData))->render();

        $storagePath = sprintf(
            'dpa-documents/%d/%s-%s.pdf',
            $paket->id,
            $kode,
            now()->format('YmdHis'),
        );

        PdfExport::saveToPublicDisk($html, $storagePath);

        return 'storage/'.$storagePath;
    }

    /**
     * @param  array<string, mixed>  $inputData
     */
    public function renderPreview(DpaPaketPekerjaan $paket, string $kode, array $inputData): string
    {
        PdfExport::ensureGdLoaded();

        $paket->loadMissing(['dpa.tahunAnggaran', 'penyedia']);

        $html = view($this->viewForKode($kode), $this->viewData($paket, $kode, $inputData))->render();

        return PdfExport::renderBinary($html);
    }

    private function viewForKode(string $kode): string
    {
        $specific = 'admin.dpa.documents.pdf.'.$kode;

        return view()->exists($specific)
            ? $specific
            : 'admin.dpa.documents.pdf.document';
    }

    /**
     * @param  array<string, mixed>  $inputData
     * @return array<string, mixed>
     */
    private function viewData(DpaPaketPekerjaan $paket, string $kode, array $inputData): array
    {
        return [
            'paket' => $paket,
            'input' => $inputData,
            'kode' => $kode,
            'judul' => DpaMonitoring::dokumenLabel($kode),
            'fields' => DpaDocumentFields::forKode($kode),
            'items' => $this->itemsForKode($paket, $kode),
            'logoBase64' => $this->logoBase64(),
        ];
    }

    /**
     * @return Collection<int, \App\Models\DpaPaketItemBelanja>
     */
    private function itemsForKode(DpaPaketPekerjaan $paket, string $kode): Collection
    {
        if ($kode === 'hps') {
            return $paket->hpsItems()->get();
        }

        if ($kode === 'spk') {
            return $paket->spkItems()->get();
        }

        return collect();
    }

    private function logoBase64(): ?string
    {
        $logoPath = public_path('images/logo-pemkot-batam.png');

        if (! is_file($logoPath)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
    }

    public function deletePublicFile(?string $filePath): void
    {
        if (blank($filePath) || ! str_starts_with($filePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($filePath, strlen('storage/')));
    }
}
