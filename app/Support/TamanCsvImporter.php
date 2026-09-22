<?php

namespace App\Support;

use App\Models\Kelurahan;
use App\Models\Taman;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TamanCsvImporter
{
    /** @var list<string> */
    public const REQUIRED_HEADERS = [
        'nama_taman',
    ];

    /** @var list<string> */
    public const OPTIONAL_FILE_HEADERS = [
        'kategori',
        'kecamatan',
        'kelurahan',
        'luasan',
        'alamat',
        'latitude',
        'longitude',
        'deskripsi',
        'fasilitas',
        'tahun_pembangunan',
        'nilai_pembangunan',
        'kontraktor',
        'konsultan_perencana',
        'data_verified_at',
    ];

    /** Kolom baca-only dari export — diabaikan saat import. */
    public const EXPORT_META_HEADERS = [
        'status_data',
        'kolom_belum_lengkap',
        'jumlah_foto_galeri',
    ];

    /** @var array<string, string> */
    private const HEADER_ALIASES = [
        'nama_tam' => 'nama_taman',
        'nama' => 'nama_taman',
        'tahun_pen' => 'tahun_pembangunan',
        'nilai_pemb' => 'nilai_pembangunan',
        'konsultan_' => 'konsultan_perencana',
        'konsultan' => 'konsultan_perencana',
        'data_verified' => 'data_verified_at',
        'verified_at' => 'data_verified_at',
        'kolom_kosong' => 'kolom_belum_lengkap',
        'field_kosong' => 'kolom_belum_lengkap',
        'status' => 'status_data',
        'jumlah_foto' => 'jumlah_foto_galeri',
        'foto_galeri' => 'jumlah_foto_galeri',
    ];

    /** @var list<string> */
    public const HEADERS = [
        ...self::REQUIRED_HEADERS,
        ...self::OPTIONAL_FILE_HEADERS,
    ];

    /**
     * @return list<string>
     */
    public function exportHeaders(): array
    {
        return [
            'id',
            ...self::HEADERS,
            ...self::EXPORT_META_HEADERS,
        ];
    }

    /**
     * @return list<string>
     */
    public function importableHeaders(): array
    {
        return [
            'id',
            ...self::HEADERS,
        ];
    }

    /**
     * @return array{imported: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            return [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['File CSV tidak dapat dibaca.'],
            ];
        }

        [$header, $delimiter] = $this->readHeader($handle);

        if ($header === null) {
            fclose($handle);

            return [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['File CSV kosong.'],
            ];
        }

        [$header, $delimiter] = $this->resolveHeaderRow($header, $delimiter);

        if (! in_array('nama_taman', $header, true)) {
            fclose($handle);

            return [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['Kolom nama_taman tidak ditemukan. Pastikan baris header ada (contoh: nama_taman atau nama_tam) dan simpan CSV dengan pemisah titik koma (;).'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            $row = $this->repairRow($row, $delimiter);
            $row = $this->padRow($row, count($header));

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $tamanId = $this->normalizeOptionalId($data['id'] ?? null);
            unset($data['id'], $data['status_data'], $data['kolom_belum_lengkap'], $data['jumlah_foto_galeri']);
            $data = $this->nullifyEmptyFields($data);
            $data['luasan'] = $this->normalizeLuasan($data['luasan'] ?? null);
            $data['nilai_pembangunan'] = $this->normalizeNilaiPembangunan($data['nilai_pembangunan'] ?? null);
            $data['tahun_pembangunan'] = $this->normalizeTahunPembangunan($data['tahun_pembangunan'] ?? null);
            $data['kategori'] = $this->normalizeKategori($data['kategori'] ?? null);
            $data['latitude'] = $this->normalizeCoordinate($data['latitude'] ?? null);
            $data['longitude'] = $this->normalizeCoordinate($data['longitude'] ?? null);
            $data['kecamatan'] = $this->normalizeWilayahName($data['kecamatan'] ?? null);
            $data['kelurahan'] = $this->normalizeWilayahName($data['kelurahan'] ?? null);
            $data['data_verified_at'] = $this->normalizeDataVerifiedAt($data['data_verified_at'] ?? null);
            $data = $this->nullifyEmptyFields($data);
            $validator = Validator::make($data, $this->rules(), $this->validationMessages());

            if ($tamanId !== null && ! Taman::query()->whereKey($tamanId)->exists()) {
                $errors[] = 'Baris '.$rowNumber.': ID taman '.$tamanId.' tidak ditemukan.';
                $skipped++;

                continue;
            }

            if ($validator->fails()) {
                $errors[] = 'Baris '.$rowNumber.': '.implode(' ', $validator->errors()->all());
                $skipped++;

                continue;
            }

            $payload = $validator->validated();
            $coordinatesProvidedInCsv = $this->hasCoordinates($payload);
            $payload = array_merge($payload, Taman::applyDefaultCoordinates(
                $payload['latitude'] ?? null,
                $payload['longitude'] ?? null,
            ));
            $kelurahan = $this->resolveKelurahan($payload, $coordinatesProvidedInCsv);

            unset($payload['kecamatan'], $payload['kelurahan']);
            $payload['kelurahan_id'] = $kelurahan?->id;

            $rawFasilitas = $payload['fasilitas'] ?? null;
            $payload['fasilitas'] = Taman::normalizeFasilitasArray($this->parseFasilitas($rawFasilitas));

            $existing = $this->resolveExistingTaman($tamanId, $payload['nama_taman']);

            if ($existing) {
                $updatePayload = $this->prepareUpdatePayload($payload, filled($rawFasilitas));
                $updatePayload = $this->applyMissingCoordinates($existing, $updatePayload, $payload, $coordinatesProvidedInCsv);

                if ($updatePayload !== []) {
                    $existing->fill($updatePayload);
                    $existing->save();
                }

                $existing->syncStatusData();
                $updated++;

                continue;
            }

            Taman::create($this->prepareCreatePayload($payload))->syncStatusData();
            $imported++;
        }

        fclose($handle);

        return compact('imported', 'updated', 'skipped', 'errors');
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
            $this->exportHeaders(),
            [
                '',
                'Taman Contoh Batam',
                '',
                '',
                'Taman Kota',
                'Batam Kota',
                'Belian',
                '5000',
                'Jl. Contoh No. 1, Batam',
                '1.045600',
                '104.030500',
                'Taman contoh untuk panduan pengisian CSV.',
                'Playground:Baik; Jogging Track:Baik',
                '2020',
                '1500000000',
                'PT Contoh Kontraktor',
                'PT Contoh Konsultan',
                '2026-08-23 10:00',
                '',
            ],
        ];
    }

    private function normalizeOptionalId(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = $this->normalizeIntegerString(trim((string) $value));

        if ($normalized === null) {
            return null;
        }

        $id = (int) $normalized;

        return $id > 0 ? $id : null;
    }

    private function resolveExistingTaman(?int $id, string $namaTaman): ?Taman
    {
        if ($id !== null) {
            $byId = Taman::query()->find($id);

            if ($byId) {
                return $byId;
            }
        }

        return Taman::query()
            ->whereRaw('LOWER(TRIM(nama_taman)) = ?', [mb_strtolower(trim($namaTaman))])
            ->first();
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

        if (preg_match('/^sep=(.*)$/i', trim($firstLine), $matches) === 1) {
            $sepRemainder = trim($matches[1]);
            $hintDelimiter = $this->parseSepDelimiterHint($sepRemainder) ?? ';';
            $headerLine = $this->extractHeaderLineFromSepRow($sepRemainder);

            if ($headerLine === null) {
                $headerLine = fgets($handle);

                if ($headerLine === false) {
                    return [null, $hintDelimiter];
                }
            }

            $headerLine = $this->stripBom($headerLine);
            $delimiter = $this->resolveDelimiter($headerLine, $hintDelimiter);

            return [str_getcsv($headerLine, $delimiter), $delimiter];
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

    private function parseSepDelimiterHint(string $sepRemainder): ?string
    {
        if ($sepRemainder === '') {
            return null;
        }

        $char = $sepRemainder[0];

        return in_array($char, [';', ',', "\t", '|'], true) ? $char : null;
    }

    private function extractHeaderLineFromSepRow(string $sepRemainder): ?string
    {
        if ($sepRemainder === '' || in_array($sepRemainder, [';', ',', "\t", '|'], true)) {
            return null;
        }

        if ($this->parseSepDelimiterHint($sepRemainder) !== null) {
            $headerLine = ltrim(substr($sepRemainder, 1));

            return $headerLine !== '' ? $headerLine : null;
        }

        return $sepRemainder;
    }

    /**
     * @param  list<string|null>  $header
     * @return array{0: list<string>, 1: string}
     */
    private function resolveHeaderRow(array $header, string $delimiter): array
    {
        $header = $this->normalizeHeader($this->repairRow($header, $delimiter));

        if (in_array('nama_taman', $header, true)) {
            return [$header, $delimiter];
        }

        if (count($header) === 1) {
            $singleLine = trim((string) ($header[0] ?? ''));

            foreach ([',', ';', "\t", '|'] as $candidate) {
                $parsed = $this->normalizeHeader(str_getcsv($singleLine, $candidate));

                if (in_array('nama_taman', $parsed, true)) {
                    return [$parsed, $candidate];
                }
            }
        }

        foreach ([',', ';', "\t", '|'] as $candidate) {
            if ($candidate === $delimiter) {
                continue;
            }

            $parsed = $this->normalizeHeader(str_getcsv(implode($delimiter, $header), $candidate));

            if (in_array('nama_taman', $parsed, true)) {
                return [$parsed, $candidate];
            }
        }

        return [$header, $delimiter];
    }

    private function detectDelimiter(string $line): string
    {
        return $this->resolveDelimiter($line);
    }

    private function resolveDelimiter(string $line, ?string $hint = null): string
    {
        $hint = ($hint !== null && $hint !== '' && strlen($hint) === 1) ? $hint : null;

        $candidates = array_values(array_unique(array_filter(
            [$hint, ';', ',', "\t", '|'],
            fn (?string $candidate) => $candidate !== null && $candidate !== ''
        )));

        $best = ';';
        $bestCount = 0;

        foreach ($candidates as $candidate) {
            $parsed = str_getcsv($line, $candidate);

            if (count($parsed) > $bestCount) {
                $bestCount = count($parsed);
                $best = $candidate;
            }
        }

        return $best;
    }

    private function canonicalHeaderColumn(string $column): string
    {
        $column = strtolower(trim($column));

        if (isset(self::HEADER_ALIASES[$column])) {
            return self::HEADER_ALIASES[$column];
        }

        foreach ($this->exportHeaders() as $known) {
            if (str_starts_with($known, $column) && strlen($column) >= 4) {
                return $known;
            }
        }

        return $column;
    }

    /**
     * @param  array<string, string|null>  $payload
     */
    private function resolveKelurahan(array $payload, bool $geocodeFromCoordinates = true): ?Kelurahan
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

        if (! $geocodeFromCoordinates || ! $this->hasCoordinates($payload)) {
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

            $column = strtolower($column);

            return $this->canonicalHeaderColumn($column);
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
        return $this->normalizeOptionalInteger($value);
    }

    private function normalizeNilaiPembangunan(?string $value): ?string
    {
        return $this->normalizeOptionalInteger($value);
    }

    private function normalizeTahunPembangunan(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $this->isPlaceholderValue($value)) {
            return null;
        }

        if (preg_match('/^\d{4}/', $value, $matches) !== 1) {
            return null;
        }

        $year = (int) $matches[0];
        $currentYear = (int) date('Y');

        if ($year < 1950 || $year > $currentYear) {
            return null;
        }

        return (string) $year;
    }

    private function normalizeOptionalInteger(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/^rp\.?\s*/iu', '', $value) ?? $value;
        $value = preg_replace('/\s*m[²2]\s*$/iu', '', $value) ?? $value;
        $value = trim($value);

        if ($value === '' || $this->isPlaceholderValue($value)) {
            return null;
        }

        return $this->parseIntegerString($value);
    }

    private function isPlaceholderValue(string $value): bool
    {
        return in_array(mb_strtolower($value), [
            '-',
            '—',
            '–',
            'n/a',
            'na',
            '#n/a',
            'null',
            'kosong',
            'none',
        ], true);
    }

    private function parseIntegerString(string $value): ?string
    {
        if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $value) === 1) {
            $normalized = str_replace('.', '', $value);
            $normalized = str_replace(',', '.', $normalized);

            return (string) (int) round((float) $normalized);
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value) === 1) {
            return str_replace('.', '', $value);
        }

        if (preg_match('/^\d{1,3}(,\d{3})+$/', $value) === 1) {
            return str_replace(',', '', $value);
        }

        if (preg_match('/^\d+,\d+$/', $value) === 1) {
            return (string) (int) round((float) str_replace(',', '.', $value));
        }

        $numeric = str_replace(',', '.', $value);

        if (is_numeric($numeric)) {
            return (string) (int) round((float) $numeric);
        }

        return null;
    }

    private function normalizeIntegerString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $this->isPlaceholderValue($value)) {
            return null;
        }

        return $this->parseIntegerString($value);
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

        if ($value === '' || $this->isPlaceholderValue($value)) {
            return null;
        }

        $value = preg_replace('/^(kelurahan|kel\.?|desa|kecamatan|kec\.?)\s+/iu', '', $value) ?? $value;

        return trim($value);
    }

    private function normalizeKategori(?string $value): ?string
    {
        if ($value === null || trim($value) === '' || $this->isPlaceholderValue(trim($value))) {
            return null;
        }

        $value = trim($value);

        $aliases = [
            'rth jalur hijau' => 'Jalur Hijau Jalan',
            'rth jalur hijau jalan' => 'Jalur Hijau Jalan',
        ];

        $key = mb_strtolower($value);
        if (isset($aliases[$key])) {
            return $aliases[$key];
        }

        foreach (Taman::KATEGORI as $kategori) {
            if (strcasecmp($value, $kategori) === 0) {
                return $kategori;
            }
        }

        return $value;
    }

    private function normalizeDataVerifiedAt(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $this->isPlaceholderValue($value)) {
            return null;
        }

        $formats = [
            'Y-m-d H:i',
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y',
            'd-m-Y H:i',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value, config('app.timezone'));

                if ($parsed !== false) {
                    return $parsed->format('Y-m-d H:i:s');
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value, config('app.timezone'))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * @param  list<string|null>  $header
     * @param  list<string|null>  $row
     * @return array<string, string|null>
     */
    private function mapRow(array $header, array $row): array
    {
        $allowed = array_merge($this->importableHeaders(), self::EXPORT_META_HEADERS);
        $mapped = array_fill_keys($allowed, null);

        foreach ($header as $index => $column) {
            if (! array_key_exists($column, $mapped)) {
                continue;
            }

            $value = $row[$index] ?? null;
            $mapped[$column] = is_string($value) ? trim($value) : (filled($value) ? trim((string) $value) : null);
        }

        return $mapped;
    }

    /**
     * @param  list<string|null>  $row
     * @return list<string|null>
     */
    private function padRow(array $row, int $length): array
    {
        while (count($row) < $length) {
            $row[] = null;
        }

        return $row;
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
     * @param  array<string, string|null>  $data
     * @return array<string, string|null>
     */
    private function nullifyEmptyFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($key === 'nama_taman') {
                continue;
            }

            if ($value === null || (is_string($value) && trim($value) === '')) {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function prepareCreatePayload(array $payload): array
    {
        $payload['kategori'] = $payload['kategori'] ?? '';
        $payload['luasan'] = $payload['luasan'] ?? 0;
        $payload['alamat'] = $payload['alamat'] ?? '';
        $payload['deskripsi'] = $payload['deskripsi'] ?? '';
        $payload = array_merge($payload, Taman::applyDefaultCoordinates(
            $payload['latitude'] ?? null,
            $payload['longitude'] ?? null,
        ));
        $payload['kelurahan_id'] = $payload['kelurahan_id'] ?? null;

        if (! filled($payload['data_verified_at'] ?? null)) {
            unset($payload['data_verified_at']);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function prepareUpdatePayload(array $payload, bool $hasFasilitasInCsv): array
    {
        $update = [];

        foreach ([
            'nama_taman',
            'kategori',
            'alamat',
            'latitude',
            'longitude',
            'deskripsi',
            'tahun_pembangunan',
            'nilai_pembangunan',
            'kontraktor',
            'konsultan_perencana',
            'data_verified_at',
        ] as $field) {
            if (($payload[$field] ?? null) !== null) {
                $update[$field] = $payload[$field];
            }
        }

        if (($payload['luasan'] ?? null) !== null && (int) $payload['luasan'] > 0) {
            $update['luasan'] = (int) $payload['luasan'];
        }

        if (($payload['kelurahan_id'] ?? null) !== null) {
            $update['kelurahan_id'] = $payload['kelurahan_id'];
        }

        if ($hasFasilitasInCsv) {
            $update['fasilitas'] = $payload['fasilitas'];
        }

        return $update;
    }

    /**
     * @param  array<string, mixed>  $updatePayload
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function applyMissingCoordinates(Taman $existing, array $updatePayload, array $payload, bool $geocodeFromCoordinates = true): array
    {
        if (filled($existing->latitude) && filled($existing->longitude)) {
            return $updatePayload;
        }

        $coords = Taman::applyDefaultCoordinates(
            $updatePayload['latitude'] ?? $payload['latitude'] ?? $existing->latitude,
            $updatePayload['longitude'] ?? $payload['longitude'] ?? $existing->longitude,
        );

        if (! filled($existing->latitude) && ! array_key_exists('latitude', $updatePayload)) {
            $updatePayload['latitude'] = $coords['latitude'];
        }

        if (! filled($existing->longitude) && ! array_key_exists('longitude', $updatePayload)) {
            $updatePayload['longitude'] = $coords['longitude'];
        }

        if (! filled($existing->kelurahan_id) && ! array_key_exists('kelurahan_id', $updatePayload) && filled($payload['kelurahan_id'] ?? null)) {
            $updatePayload['kelurahan_id'] = $payload['kelurahan_id'];
        }

        if (
            $geocodeFromCoordinates
            && ! filled($existing->kelurahan_id)
            && ! array_key_exists('kelurahan_id', $updatePayload)
        ) {
            $latitude = $updatePayload['latitude'] ?? $existing->latitude ?? $coords['latitude'];
            $longitude = $updatePayload['longitude'] ?? $existing->longitude ?? $coords['longitude'];
            $kelurahan = KelurahanResolver::findByCoordinates((float) $latitude, (float) $longitude);

            if ($kelurahan) {
                $updatePayload['kelurahan_id'] = $kelurahan->id;
            }
        }

        return $updatePayload;
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'nama_taman.required' => 'Nama taman wajib diisi.',
            'kategori.in' => 'Kategori tidak valid.',
            'luasan.integer' => 'Luasan harus angka bulat.',
            'luasan.min' => 'Luasan tidak boleh negatif.',
            'tahun_pembangunan.integer' => 'Tahun pembangunan harus angka bulat.',
            'tahun_pembangunan.min' => 'Tahun pembangunan minimal 1950.',
            'tahun_pembangunan.max' => 'Tahun pembangunan tidak boleh melebihi tahun berjalan.',
            'nilai_pembangunan.integer' => 'Nilai pembangunan harus angka bulat.',
            'nilai_pembangunan.min' => 'Nilai pembangunan tidak boleh negatif.',
            'data_verified_at.date' => 'Format waktu pemutakhiran tidak valid.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'nama_taman' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', Rule::in(Taman::KATEGORI)],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'luasan' => ['nullable', 'integer', 'min:0'],
            'alamat' => ['nullable', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'fasilitas' => ['nullable', 'string'],
            'tahun_pembangunan' => ['nullable', 'integer', 'min:1950', 'max:'.$currentYear],
            'nilai_pembangunan' => ['nullable', 'integer', 'min:0'],
            'kontraktor' => ['nullable', 'string', 'max:255'],
            'konsultan_perencana' => ['nullable', 'string', 'max:255'],
            'data_verified_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return list<array{nama: string, kondisi?: string}>|null
     */
    private function parseFasilitas(?string $fasilitas): ?array
    {
        if ($fasilitas === null || trim($fasilitas) === '') {
            return null;
        }

        $items = preg_split('/[,;|]+/', $fasilitas);
        $resolved = [];

        foreach (array_map('trim', $items ?: []) as $item) {
            if ($item === '') {
                continue;
            }

            $nama = null;
            $kondisi = 'Baik';

            if (preg_match('/^(.+?)[:](.+)$/', $item, $matches) === 1) {
                $nama = trim($matches[1]);
                $kondisi = trim($matches[2]);
            } else {
                $nama = $item;
            }

            $canonicalNama = Taman::resolveFasilitasNama($nama) ?? $nama;

            if ($canonicalNama === '') {
                continue;
            }

            $resolved[] = [
                'nama' => $canonicalNama,
                'kondisi' => $kondisi,
            ];
        }

        return $resolved === [] ? null : $resolved;
    }
}
