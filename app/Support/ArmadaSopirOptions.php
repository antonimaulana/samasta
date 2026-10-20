<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use Illuminate\Validation\Rule;

class ArmadaSopirOptions
{
    public const UNASSIGNED = 'Belum ada sopir';

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return collect(app(PetugasRosterBuilder::class)->forTeamName(PemeliharaanTaman::TIM_ARMADA))
            ->pluck('nama')
            ->filter(fn ($name) => filled($name))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<string|Rule>
     */
    /**
     * @return list<string>
     */
    public static function allowedValues(): array
    {
        return [...self::names(), self::UNASSIGNED];
    }

    /**
     * @return list<string|Rule>
     */
    public static function validationRules(bool $required): array
    {
        $allowed = self::allowedValues();

        if (self::names() === []) {
            return $required
                ? ['required', 'string', Rule::in([self::UNASSIGNED])]
                : ['nullable', 'string', 'max:100'];
        }

        return $required
            ? ['required', 'string', Rule::in($allowed)]
            : ['nullable', 'string', Rule::in($allowed)];
    }
}
