<?php

namespace App\Support;

use App\Models\Armada;
use App\Models\PemeliharaanTaman;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArmadaAssignment
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Armada>
     */
    public static function inventory(): \Illuminate\Database\Eloquent\Collection
    {
        return Armada::query()
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
                'armada.*.armada_id' => [
                    'required',
                    'integer',
                    Rule::exists('armadas', 'id')->where('peruntukan', PemeliharaanTaman::TIM_ARMADA),
                ],
                'armada.*.sopir' => ArmadaSopirOptions::validationRules(true),
            ];
        }

        return [
            'armada' => ['nullable', 'array'],
            'armada.*.armada_id' => ['nullable', 'integer', 'exists:armadas,id'],
            'armada.*.sopir' => ArmadaSopirOptions::validationRules(false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationAttributes(): array
    {
        return [
            'armada' => 'data armada',
            'armada.*.armada_id' => 'nama armada',
            'armada.*.sopir' => 'sopir',
        ];
    }

    /**
     * @return list<array{armada_id: int, jenis_armada: string, no_plat: string, sopir: string}>
     */
    public static function extractRows(Request $request, bool $required = false): array
    {
        $inputRows = collect($request->input('armada', []))
            ->filter(fn ($row) => filled($row['armada_id'] ?? null))
            ->values();

        if ($inputRows->isEmpty()) {
            return [];
        }

        $ids = $inputRows
            ->pluck('armada_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $inventory = Armada::query()
            ->armadaInventory()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $inputRows->map(function (array $row) use ($inventory) {
            $id = (int) $row['armada_id'];
            /** @var Armada|null $item */
            $item = $inventory->get($id);

            if (! $item) {
                return null;
            }

            return [
                'armada_id' => $item->id,
                'jenis_armada' => $item->jenis,
                'no_plat' => (string) ($item->no_plat ?? ''),
                'sopir' => (string) ($row['sopir'] ?? ''),
            ];
        })->filter()->values()->all();
    }

    /**
     * @param  list<array{armada_id: int, jenis_armada: string, no_plat: string, sopir: string}>  $rows
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
