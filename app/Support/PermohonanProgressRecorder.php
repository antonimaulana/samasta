<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanProgressRecorder
{
    public function update(Pemangkasan $permohonan, Request $request): Pemangkasan
    {
        if ($permohonan->status === 'Selesai') {
            abort(403, 'Permohonan ini sudah selesai dan tidak dapat diubah.');
        }

        $validated = $this->validate($request, $permohonan);
        $armadaRows = ArmadaAssignment::extractRows($request);

        $this->recordDailyProgress($permohonan, $request, $validated, $armadaRows);

        $permohonan->update([
            'status' => $validated['status'],
            'dampak' => $validated['dampak'] ?? $permohonan->dampak,
            'tanggal_penyelesaian' => $validated['status'] === 'Selesai'
                ? ($validated['tanggal_penyelesaian'] ?? now()->toDateString())
                : null,
        ]);

        PemangkasanSchedule::recalculate($permohonan);

        return $permohonan->fresh(['taman', 'progres.armadas']);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  list<array{alat_sarana_operasional_id: int, jenis_armada: string, no_plat: string, sopir: string}>  $armadaRows
     */
    private function recordDailyProgress(
        Pemangkasan $permohonan,
        Request $request,
        array $validated,
        array $armadaRows,
    ): PemangkasanProgres {
        $tanggal = $validated['tanggal_progres'];

        if (! PemangkasanSchedule::tanggalWithinSchedule($permohonan, $tanggal)) {
            throw ValidationException::withMessages([
                'tanggal_progres' => 'Tanggal progres harus berada dalam rentang jadwal pelaksanaan.',
            ]);
        }

        $entry = $permohonan->progres()
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (! $entry) {
            $entry = new PemangkasanProgres([
                'pemangkasan_id' => $permohonan->id,
                'tanggal' => $tanggal,
            ]);
        }

        $entry->hari_ke = PemangkasanSchedule::hariKe($permohonan->tanggal_eksekusi, $tanggal);
        $entry->jumlah_personil = (int) $validated['jumlah_personil'];
        $entry->catatan = $validated['catatan'] ?? null;

        foreach (PemangkasanProgres::fotoFieldKeys() as $field) {
            if ($request->hasFile($field)) {
                if ($entry->{$field}) {
                    Storage::disk('public')->delete($entry->{$field});
                }

                $entry->{$field} = $request->file($field)->store('pemangkasan/progres', 'public');
            }
        }

        $entry->save();
        ArmadaAssignment::sync($entry, $armadaRows);
        PetugasAssignment::syncFromRequest($entry, $request, is_array($permohonan->pelaksana) ? $permohonan->pelaksana : []);

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, Pemangkasan $permohonan): array
    {
        $isPohonTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';
        $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';

        $teamNames = is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [];
        $rosterExists = PetugasAssignment::rosterExistsForTeams($teamNames);

        $rules = [
            'status' => ['required', Rule::in($permohonan->lapanganStatusOptions())],
            'tanggal_progres' => ['required', 'date'],
            'jumlah_personil' => [$rosterExists ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'catatan' => ['nullable', 'string'],
            'tanggal_penyelesaian' => [
                Rule::requiredIf(fn () => $request->input('status') === 'Selesai'),
                'nullable',
                'date',
            ],
            ...ArmadaAssignment::validationRules(false),
        ];

        if ($isPohonTumbang) {
            $rules['dampak'] = ['nullable', 'string'];
        }

        $attributes = [
            'status' => 'status',
            'tanggal_progres' => 'tanggal & waktu pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'catatan' => 'catatan pekerjaan',
            'tanggal_penyelesaian' => 'tanggal penyelesaian',
            'dampak' => 'dampak',
            ...ArmadaAssignment::validationAttributes(),
        ];

        if (! $isMiniGarden) {
            foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
                $rules[$field] = [
                    'nullable',
                    'image',
                    'max:4096',
                    Rule::requiredIf(function () use ($request, $permohonan, $field) {
                        $tanggal = OperasionalPelaksanaanTime::datePart(
                            $request->input('tanggal_progres', now()->toDateString())
                        );
                        $existing = $permohonan->progres()->whereDate('tanggal', $tanggal)->first();

                        return ! $request->hasFile($field) && ! filled($existing?->{$field});
                    }),
                ];
                $attributes[$field] = strtolower($label);
            }
        }

        $rules = array_merge($rules, PetugasAssignment::validationRules($rosterExists));

        $validated = $request->validate($rules, [], array_merge($attributes, PetugasAssignment::validationAttributes()));

        unset($validated['armada'], $validated['petugas_ids']);

        $validated['tanggal_progres'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_progres']);
        PetugasAssignment::applyValidatedPersonil($validated, $request, $teamNames);

        if ($validated['status'] !== 'Selesai') {
            $validated['tanggal_penyelesaian'] = null;
        } elseif (empty($validated['tanggal_penyelesaian'])) {
            $validated['tanggal_penyelesaian'] = now()->toDateString();
        }

        return $validated;
    }
}
