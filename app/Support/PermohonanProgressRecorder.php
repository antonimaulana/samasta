<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $user = $request->user();
        $inputTim = PermohonanPelaksanaProgress::resolveInputTim(
            $user,
            $permohonan,
            $request->input('tim_pelaksana'),
        );

        if ($inputTim === null && PermohonanPelaksanaProgress::inputTeamsForUser($user, $permohonan) !== []) {
            throw ValidationException::withMessages([
                'tim_pelaksana' => 'Pilih tim pelaksana untuk progres ini.',
            ]);
        }

        $validated = $this->validate($request, $permohonan, $inputTim);
        $armadaRows = PermohonanPelaksanaProgress::shouldCollectArmada($user, $inputTim)
            ? ArmadaAssignment::extractRows($request)
            : [];

        $this->recordDailyProgress($permohonan, $request, $validated, $armadaRows, $inputTim);

        $permohonan->update([
            'status' => $validated['status'],
            'dampak' => $validated['dampak'] ?? $permohonan->dampak,
            'tanggal_penyelesaian' => $validated['status'] === 'Selesai'
                ? ($validated['tanggal_penyelesaian'] ?? now()->toDateString())
                : null,
        ]);

        if ($request->boolean('confirm_extend_schedule')) {
            $datePart = OperasionalPelaksanaanTime::datePart($validated['tanggal_progres']);
            $scheduleEnd = Carbon::parse(
                $permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi,
            )->startOfDay();

            if (Carbon::parse($datePart)->startOfDay()->gt($scheduleEnd)) {
                $permohonan->forceFill([
                    'tanggal_akhir_jadwal' => $datePart,
                    'total_hari' => PemangkasanSchedule::totalHari($permohonan->tanggal_eksekusi, $datePart),
                ])->save();
            }
        }

        PemangkasanSchedule::recalculate($permohonan);

        return $permohonan->fresh(['taman', 'progres.armadas']);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  list<array{armada_id: int, jenis_armada: string, no_plat: string, sopir: string}>  $armadaRows
     */
    private function recordDailyProgress(
        Pemangkasan $permohonan,
        Request $request,
        array $validated,
        array $armadaRows,
        ?string $inputTim,
    ): PemangkasanProgres {
        $tanggal = $validated['tanggal_progres'];
        $datePart = OperasionalPelaksanaanTime::datePart($tanggal);
        $date = Carbon::parse($datePart)->startOfDay();
        $mulai = Carbon::parse($permohonan->tanggal_eksekusi)->startOfDay();
        $akhir = Carbon::parse($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->startOfDay();
        $totalHari = (int) ($permohonan->total_hari ?? 1);
        $hariKe = PemangkasanSchedule::hariKe($permohonan->tanggal_eksekusi, $datePart);
        $confirmedEarly = $request->boolean('confirm_early_start');
        $confirmedExtend = $request->boolean('confirm_extend_schedule');

        if ($date->lt($mulai) && ! $confirmedEarly) {
            throw ValidationException::withMessages([
                'tanggal_progres' => 'Pelaksanaan sebelum jadwal memerlukan konfirmasi.',
            ]);
        }

        if (($date->gt($akhir) || $hariKe > $totalHari) && ! $confirmedExtend) {
            throw ValidationException::withMessages([
                'tanggal_progres' => 'Pelaksanaan melebihi estimasi jadwal memerlukan konfirmasi perpanjangan.',
            ]);
        }

        $entry = new PemangkasanProgres([
            'pemangkasan_id' => $permohonan->id,
            'tim' => $inputTim,
            'tanggal' => $tanggal,
        ]);

        $entry->tim = $inputTim;
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

        if ($inputTim !== null) {
            PetugasAssignment::syncFromRequestForTim($entry, $request, $inputTim);
        } else {
            $petugasTeams = is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [];
            PetugasAssignment::syncFromRequest($entry, $request, $petugasTeams);
        }

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, Pemangkasan $permohonan, ?string $inputTim): array
    {
        $isPohonTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';
        $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';

        $user = $request->user();
        $inputTeams = PermohonanPelaksanaProgress::inputTeamsForUser($user, $permohonan);
        $rosterExists = PetugasAssignment::rosterExistsForTim($inputTim);

        $rules = [
            'status' => ['required', Rule::in($permohonan->lapanganStatusOptions())],
            'tanggal_progres' => ['required', 'date'],
            'confirm_early_start' => ['nullable', 'boolean'],
            'confirm_extend_schedule' => ['nullable', 'boolean'],
            'jumlah_personil' => [$rosterExists ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'catatan' => ['nullable', 'string'],
            'tanggal_penyelesaian' => [
                Rule::requiredIf(fn () => $request->input('status') === 'Selesai'),
                'nullable',
                'date',
            ],
        ];

        if (count($inputTeams) > 1) {
            $rules['tim_pelaksana'] = ['required', 'string', Rule::in($inputTeams)];
        }

        if (PermohonanPelaksanaProgress::shouldCollectArmada($user, $inputTim)) {
            $rules = array_merge($rules, ArmadaAssignment::validationRules(false));
        }

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
            'tim_pelaksana' => 'tim pelaksana',
        ];

        if (PermohonanPelaksanaProgress::shouldCollectArmada($user, $inputTim)) {
            $attributes = array_merge($attributes, ArmadaAssignment::validationAttributes());
        }

        if (! $isMiniGarden) {
            foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
                $rules[$field] = [
                    'nullable',
                    'image',
                    'max:4096',
                    Rule::requiredIf(fn () => ! $request->hasFile($field)),
                ];
                $attributes[$field] = strtolower($label);
            }
        }

        $rules = array_merge($rules, PetugasAssignment::validationRules($rosterExists));

        $validated = $request->validate($rules, [], array_merge($attributes, PetugasAssignment::validationAttributes()));

        unset($validated['armada'], $validated['petugas_ids'], $validated['tim_pelaksana']);

        $validated['tanggal_progres'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_progres']);

        if ($inputTim !== null) {
            PetugasAssignment::applyValidatedPersonilForTim($validated, $request, $inputTim);
        } else {
            $teamNames = is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [];
            PetugasAssignment::applyValidatedPersonil($validated, $request, $teamNames);
        }

        if ($validated['status'] !== 'Selesai') {
            $validated['tanggal_penyelesaian'] = null;
        } elseif (empty($validated['tanggal_penyelesaian'])) {
            $validated['tanggal_penyelesaian'] = now()->toDateString();
        }

        return $validated;
    }
}
