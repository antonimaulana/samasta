<?php

namespace App\Support;

use App\Models\Kelurahan;
use App\Models\Taman;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TamanCsvImporter
{
    /** @var list<string> */
    public const HEADERS = [
        'nama_taman',
        'kategori',
        'kecamatan',
        'kelurahan',
        'luasan',
        'alamat',
        'latitude',
        'longitude',
        'deskripsi',
        'fasilitas',
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

        $header = $this->normalizeHeader($this->repairRow($header, $delimiter));
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
            $data['luasan'] = $this->normalizeLuasan($data['luasan'] ?? null);
            $data['kategori'] = $this->normalizeKategori($data['kategori'] ?? null);
            $data['latitude'] = $this->normalizeCoordinate($data['latitude'] ?? null);
            $data['longitude'] = $this->normalizeCoordinate($data['longitude'] ?? null);
            $data['kecamatan'] = $this->normalizeWilayahName($data['kecamatan'] ?? null);
            $data['kelurahan'] = $this->normalizeWilayahName($data['kelurahan'] ?? null);
            $validator = Validator::make($data, $this->rules());

            if ($validator->fails()) {
                $errors[] = 'Baris '.$rowNumber.': '.implode(' ', $validator->errors()->all());
                $skipped++;

                continue;
            }

            $payload = $validator->validated();
            $kelurahan = $this->resolveKelurahan($payload);

            if (! $kelurahan) {
                $errors[] = 'Baris '.$rowNumber.': wilayah tidak ditemukan. Isi kecamatan/kelurahan atau koordinat latitude/longitude yang valid.';
                $skipped++;

                continue;
            }

            unset($payload['kecamatan'], $payload['kelurahan']);
            $payload['kelurahan_id'] = $kelurahan->id;
            $payload['fasilitas'] = $this->parseFasilitas($payload['fasilitas'] ?? null);

            $exists = Taman::query()
                ->whereRaw('LOWER(TRIM(nama_taman)) = ?', [mb_strtolower(trim($payload['nama_taman']))])
                ->exists();

            if ($exists) {
                $errors[] = 'Baris '.$rowNumber.': taman "'.$payload['nama_taman'].'" sudah ada — dilewati.';
                $skipped++;

                continue;
            }

            Taman::create($payload);
            $imported++;
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
            self::HEADERS,
            [
                'Taman Contoh Batam',
                'Taman Kota',
                'Batam Kota',
                'Belian',
                '5000',
                'Jl. Contoh No. 1, Batam',
                '-1.082860',
                '104.030500',
                'Taman contoh untuk panduan pengisian CSV.',
                'Area bermain, jogging track',
            ],
        ];
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
     * @param  array<string, string|null>  $payload
     */
    private function resolveKelurahan(array $payload): ?Kelurahan
    {
        $kelurahan = KelurahanResolver::findByNames($payload['kecamatan'], $payload['kelurahan']);

        if ($kelurahan) {
            return $kelurahan;
        }

        if (filled($payload['kelurahan'] ?? null)) {
            $kelurahan = KelurahanResolver::findByKelurahanName(
                (string) $payload['kelurahan'],
                filled($payload['kecamatan'] ?? null) ? (string) $payload['kecamatan'] : null,
            );

            if ($kelurahan) {
                return $kelurahan;
            }
        }

        if (! $this->hasCoordinates($payload)) {
            return null;
        }

        return KelurahanResolver::findByCoordinates(
            (float) $payload['latitude'],
            (float) $payload['longitude'],
        );
    }

    /**
     * @param  array<string, string|null>  $payload
     */
    private function hasCoordinates(array $payload): bool
    {
        return filled($payload['latitude'] ?? null)
            && filled($payload['longitude'] ?? null)
            && is_numeric($payload['latitude'])
            && is_numeric($payload['longitude']);
    }

    /**
     * @param  list<string|null>  $header
     * @return list<string>
     */
    private function normalizeHeader(array $header): array
    {
        return array_values(array_map(function (?string $column) {
            $column = trim((string) $column);

            if (str_starts_with($column, "\xEF\xBB\xBF")) {
                $column = substr($column, 3);
            }

            return strtolower($column);
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

    private function normalizeLuasan(?string $value): ?string
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

    private function normalizeCoordinate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace(' ', '', $value));

        if ($value === '') {
            return $value;
        }

        if (preg_match('/^-?\d+,\d+$/', $value) === 1) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    private function normalizeWilayahName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/^(kelurahan|kel\.?|desa|kecamatan|kec\.?)\s+/iu', '', $value) ?? $value;

        return trim($value);
    }

    private function normalizeKategori(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        foreach (Taman::KATEGORI as $kategori) {
            if (strcasecmp($value, $kategori) === 0) {
                return $kategori;
            }
        }

        return $value;
    }

    /**
     * @param  list<string|null>  $header
     * @param  list<string|null>  $row
     * @return array<string, string|null>
     */
    private function mapRow(array $header, array $row): array
    {
        $mapped = [];

        foreach (self::HEADERS as $column) {
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
            'nama_taman' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Taman::KATEGORI)],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'luasan' => ['required', 'integer', 'min:1'],
            'alamat' => ['required', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'fasilitas' => ['nullable', 'string'],
        ];
    }

    /**
     * @return list<string>|null
     */
    private function parseFasilitas(?string $fasilitas): ?array
    {
        if ($fasilitas === null || trim($fasilitas) === '') {
            return null;
        }

        $items = preg_split('/[,;|]+/', $fasilitas);

        return array_values(array_filter(array_map('trim', $items ?: [])));
    }
}
