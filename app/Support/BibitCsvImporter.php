<?php

namespace App\Support;

use App\Models\Bibit;
use App\Models\BibitMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BibitCsvImporter
{
    /** @var list<string> */
    public const HEADERS = [
        'nama_tanaman',
        'nama_ilmiah',
        'jenis',
        'stok_awal',
    ];

    /** @var list<string> */
    public const OPTIONAL_HEADERS = [
        'sumber',
        'tanggal_masuk',
        'status_siap_tanam',
    ];

    /** @var array<string, string> */
    private const HEADER_ALIASES = [
        'nama tanaman' => 'nama_tanaman',
        'nama_tanaman' => 'nama_tanaman',
        'nama ilmiah' => 'nama_ilmiah',
        'nama_ilmiah' => 'nama_ilmiah',
        'jenis tanaman' => 'jenis',
        'jenis' => 'jenis',
        'stok awal' => 'stok_awal',
        'stok_awal' => 'stok_awal',
        'jumlah' => 'stok_awal',
        'sumber' => 'sumber',
        'tanggal masuk' => 'tanggal_masuk',
        'tanggal_masuk' => 'tanggal_masuk',
        'status siap tanam' => 'status_siap_tanam',
        'status_siap_tanam' => 'status_siap_tanam',
    ];

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function import(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File CSV tidak dapat dibaca.'],
            ];
        }

        [$header, $delimiter] = $this->readHeader($handle);

        if ($header === null) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File CSV kosong.'],
            ];
        }

        $header = $this->canonicalizeHeader($this->repairRow($header, $delimiter));
        $missing = array_diff(self::HEADERS, $header);

        if ($missing !== []) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Unduh template CSV terlebih dahulu.'],
            ];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            $row = $this->repairRow($row, $delimiter);

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $data['jenis'] = $this->normalizeJenis($data['jenis'] ?? null);
            $data['stok_awal'] = $this->normalizeInteger($data['stok_awal'] ?? null);
            $data['sumber'] = $this->normalizeSumber($data['sumber'] ?? null);
            $data['tanggal_masuk'] = $this->normalizeDate($data['tanggal_masuk'] ?? null);
            $data['status_siap_tanam'] = $this->normalizeBoolean($data['status_siap_tanam'] ?? null);

            $validator = Validator::make($data, $this->rules());

            if ($validator->fails()) {
                $errors[] = 'Baris '.$rowNumber.': '.implode(' ', $validator->errors()->all());
                $skipped++;

                continue;
            }

            $payload = $validator->validated();

            try {
                $created = $this->importRow($payload);

                if ($created) {
                    $imported++;
                } else {
                    $errors[] = 'Baris '.$rowNumber.': jenis tanaman "'.$payload['nama_tanaman'].'" tidak cocok dengan data bibit yang sudah ada.';
                    $skipped++;
                }
            } catch (\Throwable $exception) {
                $errors[] = 'Baris '.$rowNumber.': '.$exception->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors');
    }

    public function templateDelimiter(): string
    {
        return ';';
    }

    /**
     * @return list<list<string>>
     */
    public function templateRows(): array
    {
        return [
            array_merge(self::HEADERS, self::OPTIONAL_HEADERS),
            [
                'Mahoni',
                'Swietenia macrophylla',
                'Pohon Pelindung / Peneduh',
                '100',
                'Produksi',
                now()->toDateString(),
                '1',
            ],
            [
                'Bougenville',
                '',
                'Terna / Tanaman Hias Bunga',
                '50',
                'Pengadaan',
                '',
                '0',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function importRow(array $payload): bool
    {
        return DB::transaction(function () use ($payload) {
            $bibit = Bibit::query()
                ->whereRaw('LOWER(TRIM(nama_tanaman)) = ?', [mb_strtolower(trim($payload['nama_tanaman']))])
                ->lockForUpdate()
                ->first();

            if ($bibit) {
                if (strcasecmp($bibit->jenis, $payload['jenis']) !== 0) {
                    return false;
                }

                if (! $bibit->nama_ilmiah && filled($payload['nama_ilmiah'] ?? null)) {
                    $bibit->update(['nama_ilmiah' => $payload['nama_ilmiah']]);
                }
            } else {
                $bibit = Bibit::create([
                    'nama_tanaman' => trim($payload['nama_tanaman']),
                    'nama_ilmiah' => $payload['nama_ilmiah'] ?? null,
                    'jenis' => $payload['jenis'],
                    'stok_tersedia' => 0,
                    'sumber_bibit' => $payload['sumber'],
                    'status_siap_tanam' => $payload['status_siap_tanam'],
                ]);
            }

            BibitMasuk::create([
                'bibit_id' => $bibit->id,
                'jumlah' => $payload['stok_awal'],
                'sisa_stok' => $payload['stok_awal'],
                'tanggal_masuk' => $payload['tanggal_masuk'],
                'sumber' => $payload['sumber'],
                'status_siap_tanam' => $payload['status_siap_tanam'],
                'foto' => null,
            ]);

            $bibit->increment('stok_tersedia', $payload['stok_awal']);

            return true;
        });
    }

    /**
     * @param  resource  $handle
     * @return array{0: list<string|null>|null, 1: string}
     */
    private function readHeader($handle): array
    {
        $firstLine = fgets($handle);

        if ($firstLine === false) {
            return [null, ','];
        }

        $firstLine = $this->stripBom($firstLine);
        $delimiter = ',';

        if (preg_match('/^sep=(.+)$/i', trim($firstLine), $matches) === 1) {
            $delimiter = $matches[1] !== '' ? $matches[1] : ';';
            $headerLine = fgets($handle);

            if ($headerLine === false) {
                return [null, $delimiter];
            }

            return [str_getcsv($this->stripBom($headerLine), $delimiter), $delimiter];
        }

        $delimiter = $this->detectDelimiter($firstLine);

        return [str_getcsv($firstLine, $delimiter), $delimiter];
    }

    private function stripBom(string $line): string
    {
        if (str_starts_with($line, "\xEF\xBB\xBF")) {
            return substr($line, 3);
        }

        return $line;
    }

    private function detectDelimiter(string $line): string
    {
        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');
        $tabCount = substr_count($line, "\t");

        if ($semicolonCount >= $commaCount && $semicolonCount >= $tabCount && $semicolonCount > 0) {
            return ';';
        }

        if ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            return "\t";
        }

        return ',';
    }

    /**
     * @param  list<string|null>  $header
     * @return list<string>
     */
    private function canonicalizeHeader(array $header): array
    {
        return array_values(array_map(function (?string $column) {
            $column = trim((string) $column);

            if (str_starts_with($column, "\xEF\xBB\xBF")) {
                $column = substr($column, 3);
            }

            $column = strtolower($column);

            return self::HEADER_ALIASES[$column] ?? $column;
        }, $header));
    }

    /**
     * @param  list<string|null>  $row
     * @return list<string|null>
     */
    private function repairRow(array $row, string $delimiter): array
    {
        if (count($row) !== 1) {
            return $row;
        }

        $value = trim((string) ($row[0] ?? ''));

        if ($value === '') {
            return $row;
        }

        $best = $row;

        foreach (array_unique([$delimiter, ';', ',', "\t"]) as $candidate) {
            if (! str_contains($value, $candidate)) {
                continue;
            }

            $parsed = str_getcsv($value, $candidate);

            if (count($parsed) > count($best)) {
                $best = $parsed;
            }
        }

        return $best;
    }

    private function normalizeJenis(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        foreach (Bibit::JENIS as $jenis) {
            if (strcasecmp($value, $jenis) === 0) {
                return $jenis;
            }
        }

        return $value;
    }

    private function normalizeSumber(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'Produksi';
        }

        $value = trim($value);

        foreach (Bibit::SUMBER as $sumber) {
            if (strcasecmp($value, $sumber) === 0) {
                return $sumber;
            }
        }

        return $value;
    }

    private function normalizeInteger(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value) === 1) {
            return str_replace('.', '', $value);
        }

        if (preg_match('/^\d{1,3}(,\d{3})+$/', $value) === 1) {
            return str_replace(',', '', $value);
        }

        $numeric = str_replace(',', '.', $value);

        if (is_numeric($numeric)) {
            return (string) (int) round((float) $numeric);
        }

        return $value;
    }

    private function normalizeDate(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return now()->toDateString();
        }

        $value = trim($value);

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $matches) === 1) {
            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        return $value;
    }

    private function normalizeBoolean(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return false;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['1', 'true', 'ya', 'yes', 'siap'], true);
    }

    /**
     * @param  list<string>  $header
     * @param  list<string|null>  $row
     * @return array<string, string|null>
     */
    private function mapRow(array $header, array $row): array
    {
        $mapped = [];
        $columns = array_merge(self::HEADERS, self::OPTIONAL_HEADERS);

        foreach ($columns as $column) {
            $index = array_search($column, $header, true);
            $value = $index === false ? null : ($row[$index] ?? null);
            $mapped[$column] = is_string($value) ? trim($value) : (filled($value) ? trim((string) $value) : null);
        }

        return $mapped;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (filled(trim((string) $cell))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'nama_tanaman' => ['required', 'string', 'max:255'],
            'nama_ilmiah' => ['nullable', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(Bibit::JENIS)],
            'stok_awal' => ['required', 'integer', 'min:1'],
            'sumber' => ['required', Rule::in(Bibit::SUMBER)],
            'tanggal_masuk' => ['required', 'date'],
            'status_siap_tanam' => ['boolean'],
        ];
    }
}
