<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Database\Eloquent\Builder;

class TamanCsvExporter
{
    public function __construct(
        private TamanCsvImporter $importer,
        private TamanCompleteness $completeness,
    ) {}

    /**
     * @param  Builder<Taman>  $query
     * @param  resource  $handle
     */
    public function writeToStream($handle, Builder $query): void
    {
        if (! is_resource($handle)) {
            return;
        }

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fprintf($handle, 'sep=%s'.PHP_EOL, $this->importer->templateDelimiter());
        fputcsv($handle, $this->importer->exportHeaders(), $this->importer->templateDelimiter());

        $query->chunkById(200, function ($tamans) use ($handle) {
            foreach ($tamans as $taman) {
                fputcsv($handle, $this->rowFor($taman), $this->importer->templateDelimiter());
            }
        });
    }

    /**
     * @return list<string|null>
     */
    public function rowFor(Taman $taman): array
    {
        $taman->loadMissing('kelurahan.kecamatan', 'images');

        $missing = collect($this->completeness->breakdown($taman))
            ->where('filled', false)
            ->pluck('label')
            ->implode('; ');

        $galleryCount = $taman->images->count() + (filled($taman->foto) ? 1 : 0);

        $statusLabel = $taman->status_data === Taman::STATUS_DATA_LENGKAP
            ? 'Lengkap'
            : 'Belum Lengkap';

        $byHeader = [
            'id' => (string) $taman->id,
            'nama_taman' => $taman->nama_taman,
            'status_data' => $statusLabel,
            'kolom_belum_lengkap' => $missing !== '' ? $missing : null,
            'kategori' => $taman->kategori ?: null,
            'kecamatan' => $taman->kelurahan?->kecamatan?->nama,
            'kelurahan' => $taman->kelurahan?->nama,
            'luasan' => (int) $taman->luasan > 0 ? (string) (int) $taman->luasan : null,
            'alamat' => filled(trim((string) $taman->alamat)) ? $taman->alamat : null,
            'latitude' => filled($taman->latitude) ? $taman->latitude : null,
            'longitude' => filled($taman->longitude) ? $taman->longitude : null,
            'deskripsi' => filled(trim(strip_tags((string) $taman->deskripsi))) ? $taman->deskripsi : null,
            'fasilitas' => $this->formatFasilitas($taman),
            'tahun_pembangunan' => filled($taman->tahun_pembangunan) ? (string) (int) $taman->tahun_pembangunan : null,
            'nilai_pembangunan' => filled($taman->nilai_pembangunan) ? (string) (int) $taman->nilai_pembangunan : null,
            'kontraktor' => filled($taman->kontraktor) ? $taman->kontraktor : null,
            'konsultan_perencana' => filled($taman->konsultan_perencana) ? $taman->konsultan_perencana : null,
            'data_verified_at' => $taman->data_verified_at?->format('Y-m-d H:i'),
            'jumlah_foto_galeri' => $galleryCount > 0 ? (string) $galleryCount : '0',
        ];

        return collect($this->importer->exportHeaders())
            ->map(fn (string $header) => $byHeader[$header] ?? null)
            ->values()
            ->all();
    }

    private function formatFasilitas(Taman $taman): ?string
    {
        $items = collect($taman->fasilitas_items);

        if ($items->isEmpty()) {
            return null;
        }

        return $items
            ->map(fn (array $item) => trim($item['nama']).':'.trim($item['kondisi'] ?? 'Baik'))
            ->implode('; ');
    }
}
