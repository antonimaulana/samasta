<?php

namespace App\Support;

use App\Models\Taman;

class TamanCsvExporter
{
    public function __construct(
        private TamanCsvImporter $importer,
        private TamanCompleteness $completeness,
    ) {}

    /**
     * @param  resource  $handle
     * @param  list<int>  $tamanIds
     */
    public function writeToStream($handle, array $tamanIds): void
    {
        if (! is_resource($handle)) {
            return;
        }

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fprintf($handle, 'sep=%s'.PHP_EOL, $this->importer->templateDelimiter());
        fputcsv($handle, $this->importer->exportHeaders(), $this->importer->templateDelimiter());

        if ($tamanIds === []) {
            return;
        }

        foreach (array_chunk($tamanIds, 200) as $idChunk) {
            $byId = Taman::query()
                ->with(['kelurahan.kecamatan', 'images'])
                ->whereIn('id', $idChunk)
                ->get()
                ->keyBy('id');

            foreach ($idChunk as $id) {
                $taman = $byId->get($id);

                if ($taman === null) {
                    continue;
                }

                fputcsv($handle, $this->rowFor($taman), $this->importer->templateDelimiter());
            }
        }
    }

    /**
     * @return list<string|null>
     */
    public function rowFor(Taman $taman): array
    {
        $taman->loadMissing('kelurahan.kecamatan', 'images');

        $breakdown = $this->completeness->breakdown($taman);
        $missing = collect($breakdown)
            ->where('filled', false)
            ->pluck('label')
            ->implode('; ');

        $galleryCount = $taman->images->count() + (filled($taman->foto) ? 1 : 0);
        $computedStatus = $this->completeness->statusFor($taman);
        $statusLabel = $computedStatus === Taman::STATUS_DATA_LENGKAP
            ? 'Lengkap'
            : 'Belum Lengkap';

        $byHeader = [
            'id' => (string) $taman->id,
            'nama_taman' => $taman->nama_taman,
            'status_data' => $statusLabel,
            'kolom_belum_lengkap' => $missing !== '' ? $missing : null,
            'kategori' => $this->exportKategori($taman->kategori),
            'kecamatan' => $this->exportText($taman->kelurahan?->kecamatan?->nama),
            'kelurahan' => $this->exportText($taman->kelurahan?->nama),
            'luasan' => (string) (int) $taman->luasan,
            'alamat' => $this->exportText($taman->alamat),
            'latitude' => $this->exportText($taman->latitude),
            'longitude' => $this->exportText($taman->longitude),
            'deskripsi' => $this->exportText($taman->deskripsi),
            'fasilitas' => $this->formatFasilitas($taman),
            'tahun_pembangunan' => $taman->tahun_pembangunan !== null
                ? (string) (int) $taman->tahun_pembangunan
                : null,
            'nilai_pembangunan' => $taman->nilai_pembangunan !== null
                ? (string) (int) $taman->nilai_pembangunan
                : null,
            'kontraktor' => $this->exportText($taman->kontraktor),
            'konsultan_perencana' => $this->exportText($taman->konsultan_perencana),
            'data_verified_at' => $taman->data_verified_at?->format('Y-m-d H:i'),
            'jumlah_foto_galeri' => (string) $galleryCount,
        ];

        return collect($this->importer->exportHeaders())
            ->map(fn (string $header) => $byHeader[$header] ?? null)
            ->values()
            ->all();
    }

    private function exportText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function exportKategori(mixed $value): ?string
    {
        $text = $this->exportText($value);

        if ($text === null) {
            return null;
        }

        foreach (Taman::KATEGORI as $kategori) {
            if (strcasecmp($text, $kategori) === 0) {
                return $kategori;
            }
        }

        return $text;
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
