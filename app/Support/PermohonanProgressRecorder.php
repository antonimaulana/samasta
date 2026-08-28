<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
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

        $this->recordDailyProgress($permohonan, $request, $validated);

        $permohonan->update([
            'status' => $validated['status'],
            'dampak' => $validated['dampak'] ?? $permohonan->dampak,
            'tanggal_penyelesaian' => $validated['status'] === 'Selesai'
                ? ($validated['tanggal_penyelesaian'] ?? now()->toDateString())
                : null,
        ]);

        ArmadaAssignment::sync($permohonan, $armadaRows);
        PemangkasanSchedule::recalculate($permohonan);

        return $permohonan->fresh(['taman', 'armadas', 'progres']);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function recordDailyProgress(Pemangkasan $permohonan, Request $request, array $validated): PemangkasanProgres
    {
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

        foreach (['foto_sebelum', 'foto_saat', 'foto_sesudah'] as $field) {
            if ($request->hasFile($field)) {
                if ($entry->{$field}) {
                    Storage::disk('public')->delete($entry->{$field});
                }

                $entry->{$field} = $request->file($field)->store('pemangkasan/progres', 'public');
            }
        }

        $entry->save();

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request, Pemangkasan $permohonan): array
    {
        $isPohonTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';
        $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';

        $rules = [
            'status' => ['required', Rule::in($permohonan->lapanganStatusOptions())],
            'tanggal_progres' => ['required', 'date'],
            'jumlah_personil' => ['required', 'integer', 'min:1', 'max:9999'],
            'catatan' => ['nullable', 'string'],
            'foto_sebelum' => ['nullable', 'image', 'max:4096'],
            'foto_saat' => ['nullable', 'image', 'max:4096'],
            'foto_sesudah' => ['nullable', 'image', 'max:4096'],
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

        $inputStatus = (string) $request->input('status');

        if (! $isMiniGarden) {
            if ($inputStatus !== 'Rencana') {
                $rules['foto_sebelum'][] = Rule::requiredIf(function () use ($request, $permohonan) {
                    $tanggal = $request->input('tanggal_progres', now()->toDateString());
                    $existing = $permohonan->progres()->whereDate('tanggal', $tanggal)->first();

                    return ! $request->hasFile('foto_sebelum') && ! $existing?->foto_sebelum;
                });
                $rules['foto_saat'][] = Rule::requiredIf(function () use ($request, $permohonan) {
                    $tanggal = $request->input('tanggal_progres', now()->toDateString());
                    $existing = $permohonan->progres()->whereDate('tanggal', $tanggal)->first();

                    return ! $request->hasFile('foto_saat') && ! $existing?->foto_saat;
                });
            }

            if ($inputStatus !== 'Selesai') {
                $rules['foto_sesudah'][] = Rule::requiredIf(function () use ($request, $permohonan) {
                    $tanggal = $request->input('tanggal_progres', now()->toDateString());
                    $existing = $permohonan->progres()->whereDate('tanggal', $tanggal)->first();

                    return ! $request->hasFile('foto_sesudah') && ! $existing?->foto_sesudah;
                });
            }
        }

        $validated = $request->validate($rules, [], array_merge([
            'status' => 'status',
            'tanggal_progres' => 'tanggal progres',
            'jumlah_personil' => 'jumlah personil',
            'catatan' => 'catatan pekerjaan',
            'foto_sebelum' => 'foto sebelum pelaksanaan',
            'foto_saat' => 'foto saat pelaksanaan',
            'foto_sesudah' => 'foto sesudah pelaksanaan',
            'tanggal_penyelesaian' => 'tanggal penyelesaian',
            'dampak' => 'dampak',
        ], ArmadaAssignment::validationAttributes()));

        unset($validated['armada']);

        if ($validated['status'] !== 'Selesai') {
            $validated['tanggal_penyelesaian'] = null;
        } elseif (empty($validated['tanggal_penyelesaian'])) {
            $validated['tanggal_penyelesaian'] = now()->toDateString();
        }

        return $validated;
    }
}
