<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemeliharaanTamanRecorder
{
    public function record(Request $request, ?User $user = null): PemeliharaanTaman
    {
        $validated = $this->validate($request, $user);
        $this->applyTamanLokasi($request, $validated);
        $this->storeUploadedFotos($request, $validated);
        $armadaRows = ArmadaAssignment::extractRows(
            $request,
            ArmadaAssignment::requiresArmadaForTim($validated['tim']),
        );

        $pemeliharaan = PemeliharaanTaman::create($validated);
        ArmadaAssignment::sync($pemeliharaan, $armadaRows);

        return $pemeliharaan;
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(Request $request, ?User $user = null): array
    {
        $validated = $this->validateFields($request);

        if ($user) {
            app(OperatorWilayahScope::class)->enforceOperatorTim($user, $validated);
        }

        if (! $request->boolean('lokasi_luar')) {
            $this->ensureTamanInTeamWilayah($validated['tim'], (int) $validated['taman_id']);
        }

        return $validated;
    }

    private function ensureTamanInTeamWilayah(string $tim, int $tamanId): void
    {
        if (! app(TimPelaksanaResolver::class)->tamanAllowedForTeam($tim, $tamanId)) {
            throw ValidationException::withMessages([
                'taman_id' => 'Lokasi pelaksanaan tidak termasuk wilayah kerja '.$tim.'.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFields(Request $request): array
    {
        $isLokasiLuar = $request->boolean('lokasi_luar');
        $tim = (string) $request->input('tim');
        $requiresArmada = ArmadaAssignment::requiresArmadaForTim($tim);

        $rules = [
            'tanggal' => ['required', 'date'],
            'tim' => ['required', Rule::in(PemeliharaanTaman::timNames())],
            'lokasi_luar' => ['nullable', 'boolean'],
            'jumlah_personil' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'hari_ke' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'total_hari' => ['nullable', 'integer', 'min:1', 'max:9999', 'gte:hari_ke'],
            'persentase_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
            'uraian_pekerjaan' => ['nullable', 'string'],
            ...ArmadaAssignment::validationRules($requiresArmada),
        ];

        if ($isLokasiLuar) {
            $rules['lokasi_pelaksanaan'] = ['required', 'string', 'max:255'];
        } else {
            $rules['taman_id'] = ['required', 'exists:tamans,id'];
        }

        $attributes = [
            'tanggal' => 'tanggal pelaksanaan',
            'tim' => 'tim',
            'taman_id' => 'lokasi pelaksanaan',
            'lokasi_pelaksanaan' => 'lokasi pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'hari_ke' => 'hari ke',
            'total_hari' => 'total hari',
            'persentase_progres' => 'persentase progres',
            'uraian_pekerjaan' => 'uraian pekerjaan',
            ...ArmadaAssignment::validationAttributes(),
        ];

        foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
            $rules[$field] = ['required', 'image', 'max:4096'];
            $attributes[$field] = strtolower($label);
        }

        $validated = $request->validate($rules, [], $attributes);

        unset($validated['armada'], $validated['lokasi_luar']);

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function applyTamanLokasi(Request $request, array &$validated): void
    {
        if ($request->boolean('lokasi_luar')) {
            $validated['taman_id'] = null;
        } else {
            $taman = Taman::query()->findOrFail($validated['taman_id']);
            $validated['lokasi_pelaksanaan'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function storeUploadedFotos(Request $request, array &$validated): void
    {
        foreach (PemeliharaanTaman::fotoFieldKeys() as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $validated[$field] = $request->file($field)->store('pemeliharaan-taman', 'public');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Taman>
     */
    public function tamanOptions(?User $user = null)
    {
        $query = Taman::query()->orderBy('nama_taman');

        if ($user) {
            $query = app(OperatorWilayahScope::class)->scopeTamans($query, $user);
        }

        return $query->get(['id', 'nama_taman', 'alamat', 'kategori', 'kelurahan_id']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Taman>
     */
    public function tamanOptionsForTeam(?User $user, string $tim)
    {
        $tamans = $this->tamanOptions($user);
        $allowedKelurahanIds = app(TimPelaksanaResolver::class)->kelurahanIdsForTeamName($tim);

        if ($allowedKelurahanIds === null) {
            return $tamans;
        }

        $allowedSet = array_flip($allowedKelurahanIds);

        return $tamans
            ->filter(fn (Taman $taman) => $taman->kelurahan_id && isset($allowedSet[(int) $taman->kelurahan_id]))
            ->values();
    }
}
