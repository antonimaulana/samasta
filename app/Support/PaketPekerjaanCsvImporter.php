<?php

namespace App\Support;

use App\Models\DpaPaketPekerjaan;
use Illuminate\Support\Facades\Validator;

class PaketPekerjaanCsvImporter
{
    /** @var list<string> */
    public const HEADERS = [
        'nomor_rekening',
        'nama_rekening',
        'nama_paket_pekerjaan',
        'pagu_anggaran',
        'rincian_item_belanja',
        'kode_rup',
        'jenis_pengadaan',
        'metode_pemilihan',
        'anggaran_kas',
        'masa_pelaksanaan',
    ];

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function import(string $absolutePath, int $dpaId): array
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['File CSV tidak dapat dibaca.']];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['File CSV kosong.']];
        }

        $delimiter = str_contains($firstLine, ';') ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['File CSV kosong.']];
        }

        $header = array_map(function ($col) {
            $col = (string) $col;
            $col = preg_replace('/^\xEF\xBB\xBF/', '', $col) ?? $col;

            return strtolower(trim($col));
        }, $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            if ($row === [null] || trim(implode('', array_map('strval', $row))) === '') {
                continue;
            }

            $data = [];
            foreach ($header as $index => $column) {
                $data[$column] = trim((string) ($row[$index] ?? ''));
            }

            $validator = Validator::make($data, [
                'nama_paket_pekerjaan' => ['required', 'string', 'max:255'],
                'nomor_rekening' => ['nullable', 'string', 'max:100'],
                'nama_rekening' => ['nullable', 'string', 'max:255'],
                'pagu_anggaran' => ['nullable'],
                'rincian_item_belanja' => ['nullable', 'string'],
                'kode_rup' => ['nullable', 'string', 'max:100'],
                'jenis_pengadaan' => ['nullable', 'string', 'max:100'],
                'metode_pemilihan' => ['nullable', 'string', 'max:100'],
                'anggaran_kas' => ['nullable'],
                'masa_pelaksanaan' => ['nullable', 'string', 'max:255'],
            ], [], [
                'nama_paket_pekerjaan' => 'nama paket pekerjaan',
            ]);

            if ($validator->fails()) {
                $skipped++;
                $errors[] = 'Baris '.$line.': '.$validator->errors()->first();

                continue;
            }

            $validated = $validator->validated();
            $paguRaw = (string) ($validated['pagu_anggaran'] ?? '0');
            $kasRaw = (string) ($validated['anggaran_kas'] ?? '');

            DpaPaketPekerjaan::create([
                'dpa_id' => $dpaId,
                'nomor_rekening' => $validated['nomor_rekening'] ?? null,
                'nama_rekening' => $validated['nama_rekening'] ?? null,
                'nama_paket' => $validated['nama_paket_pekerjaan'],
                'pagu_anggaran' => (int) preg_replace('/\D/', '', $paguRaw),
                'rincian_item_belanja' => $validated['rincian_item_belanja'] ?? null,
                'kode_rup' => $validated['kode_rup'] ?? null,
                'jenis_pengadaan' => $validated['jenis_pengadaan'] ?? null,
                'metode_pemilihan' => $validated['metode_pemilihan'] ?? null,
                'anggaran_kas' => filled($kasRaw) ? (int) preg_replace('/\D/', '', $kasRaw) : null,
                'masa_pelaksanaan' => $validated['masa_pelaksanaan'] ?? null,
                'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
            ]);

            $imported++;
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @return list<list<string>>
     */
    public function templateRows(): array
    {
        return [
            self::HEADERS,
            [
                '5.1.02.04.01.0001',
                'Belanja Barang',
                'Contoh Paket Pemeliharaan Taman',
                '500000000',
                'Honor pekerja lapangan',
                'RUP-2026-001',
                'Jasa Lainnya',
                'Pengadaan Langsung',
                '500000000',
                'Januari - Desember 2026',
            ],
        ];
    }

    public function templateDelimiter(): string
    {
        return ';';
    }
}
