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
        $armadaRows = ArmadaAssignment::extractRows($request);
        $tanggal = $validated['tanggal'];

        if ($permohonan->progres()
            ->where('id', '!=', $entry->id)
            ->whereDate('tanggal', $tanggal)
            ->exists()) {
            throw ValidationException::withMessages([
                'tanggal' => 'Sudah ada progres untuk tanggal tersebut.',
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
        PetugasAssignment::syncFromRequest(
            $entry,
            $request,
            is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [],
        );
        PemangkasanSchedule::recalculate($permohonan);

        return $entry->fresh(['armadas', 'petugas']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, Pemangkasan $permohonan, PemangkasanProgres $entry): array
    {
        $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';

        $teamNames = is_array($permohonan->pelaksana) ? $permohonan->pelaksana : [];
        $rosterExists = PetugasAssignment::rosterExistsForTeams($teamNames);

        $rules = [
            'tanggal' => ['required', 'date'],
            'jumlah_personil' => [$rosterExists ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'catatan' => ['nullable', 'string'],
            ...ArmadaAssignment::validationRules(false),
        ];

        $attributes = [
            'tanggal' => 'tanggal & waktu pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'catatan' => 'catatan pekerjaan',
            ...ArmadaAssignment::validationAttributes(),
        ];

        if (! $isMiniGarden) {
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
        PetugasAssignment::applyValidatedPersonil($validated, $request, $teamNames);

        if (! PemangkasanSchedule::tanggalWithinSchedule($permohonan, $validated['tanggal'])) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal progres harus berada dalam rentang jadwal pelaksanaan.',
            ]);
        }

        return $validated;
    }
}
