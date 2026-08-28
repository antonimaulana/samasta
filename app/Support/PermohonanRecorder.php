<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermohonanRecorder
{
    public function record(Request $request): Pemangkasan
    {
        $requiresArmada = ArmadaAssignment::requiresArmadaForPelaksana($request->input('pelaksana', []));
        $validated = $this->validate($request, $requiresArmada);
        $armadaRows = ArmadaAssignment::extractRows($request, $requiresArmada);

        if ($request->hasFile('foto_sebelum')) {
            $validated['foto_sebelum'] = $request->file('foto_sebelum')->store('pemangkasan', 'public');
        }

        if ($request->hasFile('foto_sesudah')) {
            $validated['foto_sesudah'] = $request->file('foto_sesudah')->store('pemangkasan', 'public');
        }

        if ($request->hasFile('pendukung_pelaksanaan')) {
            $validated['pendukung_pelaksanaan'] = $request->file('pendukung_pelaksanaan')
                ->store('pemangkasan/pendukung', 'public');
        }

        $permohonan = Pemangkasan::create($validated);
        ArmadaAssignment::sync($permohonan, $armadaRows);

        return $permohonan;
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, bool $requiresArmada): array
    {
        $isMiniGarden = $request->input('jenis_layanan') === 'Pemasangan Mini Garden';
        $isLokasiLuar = $request->boolean('lokasi_luar');

        $rules = [
            'jenis_layanan' => ['required', Rule::in(Pemangkasan::JENIS_LAYANAN)],
            'lokasi_luar' => ['nullable', 'boolean'],
            'asal' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'kontak_permohonan' => ['required', 'string', 'min:9', 'max:50', 'regex:/^[0-9+\s\-]+$/'],
            'tanggal_permohonan' => ['required', 'date'],
            'kategori' => ['required', Rule::in(Pemangkasan::KATEGORI)],
            'dampak' => ['nullable', 'string'],
            'pendukung_pelaksanaan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:5120'],
            'tanggal_eksekusi' => ['required', 'date'],
            'tanggal_penyelesaian' => [
                Rule::requiredIf(fn () => $request->input('status') === 'Selesai'),
                'nullable',
                'date',
            ],
            'pelaksana' => ['required', 'array', 'min:1'],
            'pelaksana.*' => ['required', 'string', Rule::in(PemeliharaanTaman::timNames())],
            'status' => ['required', Rule::in(Pemangkasan::STATUS)],
            'foto_sebelum' => ['nullable', 'image', 'max:2048'],
            'foto_sesudah' => ['nullable', 'image', 'max:2048'],
            ...ArmadaAssignment::validationRules($requiresArmada),
        ];

        if (! $isMiniGarden) {
            if ($isLokasiLuar) {
                $rules['lokasi_pohon'] = ['required', 'string', 'max:255'];
            } else {
                $rules['taman_id'] = ['required', 'exists:tamans,id'];
            }
        }

        $validated = $request->validate($rules, [], array_merge([
            'pelaksana' => 'pelaksana',
            'pelaksana.*' => 'tim pelaksana',
            'penanggungjawab' => 'penanggung jawab',
            'kontak_permohonan' => 'kontak permohonan',
            'pendukung_pelaksanaan' => 'pendukung pelaksanaan',
            'foto_sebelum' => 'foto sebelum pelaksanaan',
            'foto_sesudah' => 'foto setelah pelaksanaan',
            'tanggal_eksekusi' => 'jadwal pelaksanaan',
            'tanggal_penyelesaian' => 'tanggal penyelesaian',
            'taman_id' => 'lokasi taman',
            'lokasi_pohon' => 'lokasi',
        ], ArmadaAssignment::validationAttributes()));

        if ($isMiniGarden) {
            $validated['taman_id'] = null;
            $validated['lokasi_pohon'] = 'Pemasangan Mini Garden';
        } elseif ($isLokasiLuar) {
            $validated['taman_id'] = null;
        } else {
            $taman = Taman::query()->findOrFail($validated['taman_id']);
            $validated['lokasi_pohon'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);
        }

        if ($validated['status'] !== 'Selesai') {
            $validated['tanggal_penyelesaian'] = null;
        }

        unset($validated['lokasi_luar'], $validated['armada']);
        $validated['kondisi_sebelum'] = '';

        return $validated;
    }
}
