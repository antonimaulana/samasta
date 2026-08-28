<?php

namespace App\Support;

use App\Models\AlatSaranaOperasional;
use App\Models\PemeliharaanTaman;
use App\Models\PemeliharaanTamanArmada;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArmadaAssignment
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, AlatSaranaOperasional>
     */
    public static function inventory(): \Illuminate\Database\Eloquent\Collection
    {
        return AlatSaranaOperasional::query()
            ->armadaInventory()
            ->orderBy('nama')
            ->get(['id', 'nama', 'jenis']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $required): array
    {
        if ($required) {
            return [
                'armada' => ['required', 'array', 'min:1'],
                'armada.*.alat_sarana_operasional_id' => [
                    'required',
                    'integer',
                    Rule::exists('alat_sarana_operasionals', 'id')->where('peruntukan', PemeliharaanTaman::TIM_ARMADA),
                ],
                'armada.*.sopir' => ['required', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)],
            ];
        }

        return [
            'armada' => ['nullable', 'array'],
            'armada.*.alat_sarana_operasional_id' => ['nullable', 'integer', 'exists:alat_sarana_operasionals,id'],
            'armada.*.sopir' => ['nullable', 'string', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationAttributes(): array
    {
        return [
            'armada' => 'data armada',
            'armada.*.alat_sarana_operasional_id' => 'nama armada',
            'armada.*.sopir' => 'sopir',
        ];
    }

    /**
     * @return list<array{alat_sarana_operasional_id: int, jenis_armada: string, no_plat: string, sopir: string}>
     */
    public static function extractRows(Request $request, bool $required = false): array
    {
        $inputRows = collect($request->input('armada', []))
            ->filter(fn ($row) => filled($row['alat_sarana_operasional_id'] ?? null))
            ->values();

        if ($inputRows->isEmpty()) {
            return [];
        }

        $ids = $inputRows
            ->pluck('alat_sarana_operasional_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $inventory = AlatSaranaOperasional::query()
            ->armadaInventory()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $inputRows->map(function (array $row) use ($inventory) {
            $id = (int) $row['alat_sarana_operasional_id'];
            /** @var AlatSaranaOperasional|null $item */
            $item = $inventory->get($id);

            if (! $item) {
                return null;
            }

            return [
                'alat_sarana_operasional_id' => $item->id,
                'jenis_armada' => $item->jenis,
                'no_plat' => (string) ($item->no_plat ?? ''),
                'sopir' => (string) ($row['sopir'] ?? ''),
            ];
        })->filter()->values()->all();
    }

    /**
     * @param  list<array{alat_sarana_operasional_id: int, jenis_armada: string, no_plat: string, sopir: string}>  $rows
     */
    public static function sync(Model $owner, array $rows, string $relation = 'armadas'): void
    {
        $owner->{$relation}()->delete();

        foreach ($rows as $index => $row) {
            $owner->{$relation}()->create([
                ...$row,
                'urutan' => $index,
            ]);
        }
    }

    public static function requiresArmadaForTim(string $tim): bool
    {
        return $tim === PemeliharaanTaman::TIM_ARMADA;
    }

    /**
     * @param  list<string>  $pelaksana
     */
    public static function requiresArmadaForPelaksana(array $pelaksana): bool
    {
        return in_array(PemeliharaanTaman::TIM_ARMADA, $pelaksana, true);
    }
}
