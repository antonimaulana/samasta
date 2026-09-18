<?php

namespace App\Support;

use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PetugasAssignment
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validationRules(bool $requiredWhenRosterExists = true): array
    {
        $rules = ['array'];

        if ($requiredWhenRosterExists) {
            $rules = array_merge(['required', 'min:1'], $rules);
        } else {
            $rules = array_merge(['nullable'], $rules);
        }

        return [
            'petugas_ids' => $rules,
            'petugas_ids.*' => ['integer', 'exists:petugas,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationAttributes(): array
    {
        return [
            'petugas_ids' => 'petugas pelaksana',
            'petugas_ids.*' => 'petugas pelaksana',
        ];
    }

    /**
     * @param  list<string>  $teamNames
     * @return list<int>
     */
    public static function extractIds(Request $request): array
    {
        return collect($request->input('petugas_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $teamNames
     * @return list<int>
     */
    public static function validateForTeams(array $teamNames, array $petugasIds): array
    {
        if ($petugasIds === []) {
            return [];
        }

        $allowedIds = collect(app(PetugasRosterBuilder::class)->forTeamNames($teamNames))
            ->pluck('id')
            ->all();

        $invalid = array_diff($petugasIds, $allowedIds);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'petugas_ids' => 'Ada petugas yang tidak termasuk tim pelaksana kegiatan ini.',
            ]);
        }

        return $petugasIds;
    }

    public static function rosterExistsForTeams(array $teamNames): bool
    {
        return app(PetugasRosterBuilder::class)->forTeamNames($teamNames) !== [];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  list<string>  $teamNames
     */
    public static function applyValidatedPersonil(array &$validated, Request $request, array $teamNames): void
    {
        $rosterExists = self::rosterExistsForTeams($teamNames);

        if (! $rosterExists) {
            return;
        }

        $petugasIds = self::validateForTeams($teamNames, self::extractIds($request));

        if ($petugasIds === []) {
            throw ValidationException::withMessages([
                'petugas_ids' => 'Pilih minimal satu petugas pelaksana.',
            ]);
        }

        $validated['jumlah_personil'] = count($petugasIds);
    }

    /**
     * @param  PemeliharaanTaman|PemangkasanProgres  $record
     * @param  list<int>  $petugasIds
     */
    public static function sync(Model $record, array $petugasIds): void
    {
        if ($petugasIds === []) {
            if ($record instanceof PemeliharaanTaman || $record instanceof PemangkasanProgres) {
                $record->petugas()->sync([]);
            }

            return;
        }

        $record->petugas()->sync($petugasIds);
    }

    public static function syncFromRequest(Model $record, Request $request, array $teamNames): void
    {
        if (! self::rosterExistsForTeams($teamNames)) {
            $record->petugas()->sync([]);

            return;
        }

        $petugasIds = self::validateForTeams($teamNames, self::extractIds($request));
        self::sync($record, $petugasIds);
    }
}
