<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PermohonanProgresEntryRecorder
{
    public function update(Pemangkasan $permohonan, PemangkasanProgres $entry, Request $request): PemangkasanProgres
    {
        if ($entry->pemangkasan_id !== $permohonan->id) {
            abort(404);
        }

        $validated = $this->validate($request, $permohonan, $entry);
        $collectArmada = filled($entry->tim)
            ? $entry->tim === PemeliharaanTaman::TIM_ARMADA
            : $permohonan->usesTimArmada();
        $armadaRows = $collectArmada ? ArmadaAssignment::extractRows($request) : [];
        $tanggal = $validated['tanggal'];

        $duplicateQuery = $permohonan->progres()
            ->where('id', '!=', $entry->id)
            ->whereDate('tanggal', OperasionalPelaksanaanTime::datePart($tanggal));

        $tim = $entry->tim;
        if ($tim !== null) {
            $duplicateQuery->where('tim', $tim);
        } else {
            $duplicateQuery->whereNull('tim');
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'tanggal' => 'Sudah ada progres untuk tim dan tanggal tersebut.',
            ]);
        }

        $entry->tanggal = $tanggal;
        $entry->hari_ke = PemangkasanSchedule::hariKe($permohonan->tanggal_eksekusi, $tanggal);
        $entry->jumlah_personil = (int) $validated['jumlah_personil'];
        $entry->catatan = $validated['catatan'] ?? null;

        foreach (PemangkasanProgres::fotoFieldKeys() as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            if ($entry->{$field}) {
                Storage::disk('public')->delete($entry->{$field});
            }

            $entry->{$field} = $request->file($field)->store('pemangkasan/progres', 'public');
        }

        $entry->save();
        ArmadaAssignment::sync($entry, $armadaRows);

        if (filled($entry->tim)) {
            PetugasAssignment::syncFromRequestForTim($entry, $request, $entry->tim);
        } else {
            PetugasAssignment::syncFromRequest(
                $entry,
                $request,
                is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [],
            );
        }

        PemangkasanSchedule::recalculate($permohonan);

        return $entry->fresh(['armadas', 'petugas']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, Pemangkasan $permohonan, PemangkasanProgres $entry): array
    {
        $teamNames = is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [];
        $inputTim = filled($entry->tim) ? $entry->tim : null;
        $collectFoto = PermohonanPelaksanaProgress::shouldCollectFotoDocumentation($permohonan, $inputTim);
        $rosterExists = $inputTim !== null
            ? PetugasAssignment::rosterExistsForTim($inputTim)
            : PetugasAssignment::rosterExistsForTeams($teamNames);
        $collectArmada = $inputTim !== null
            ? $inputTim === PemeliharaanTaman::TIM_ARMADA
            : $permohonan->usesTimArmada();

        $rules = [
            'tanggal' => ['required', 'date'],
            'jumlah_personil' => [$rosterExists ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'catatan' => ['nullable', 'string'],
        ];

        $attributes = [
            'tanggal' => 'tanggal & waktu pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'catatan' => 'catatan pekerjaan',
        ];

        if ($collectArmada) {
            $rules = array_merge($rules, ArmadaAssignment::validationRules(false));
            $attributes = array_merge($attributes, ArmadaAssignment::validationAttributes());
        }

        if ($collectFoto) {
            foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
                $rules[$field] = ['nullable', 'image', 'max:4096'];

                if (! $entry->{$field}) {
                    $rules[$field] = ['required', 'image', 'max:4096'];
                }

                $attributes[$field] = strtolower($label);
            }
        }

        $rules = array_merge($rules, PetugasAssignment::validationRules($rosterExists));

        $validated = $request->validate($rules, [], array_merge($attributes, PetugasAssignment::validationAttributes()));

        unset($validated['petugas_ids']);

        $validated['tanggal'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal']);

        if ($inputTim !== null) {
            PetugasAssignment::applyValidatedPersonilForTim($validated, $request, $inputTim);
        } else {
            PetugasAssignment::applyValidatedPersonil($validated, $request, $teamNames);
        }

        if (! PemangkasanSchedule::tanggalWithinSchedule($permohonan, $validated['tanggal'])) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal progres harus berada dalam rentang jadwal pelaksanaan.',
            ]);
        }

        return $validated;
    }
}
