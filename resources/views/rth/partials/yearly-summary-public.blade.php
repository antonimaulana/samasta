@php
    use App\Support\PublicRthStatisticsBuilder;

    $formatValue = function (array $row, int $year): string {
        $value = $row['values'][$year] ?? null;

        return match ($row['format']) {
            'area' => PublicRthStatisticsBuilder::formatArea($value !== null ? (int) $value : null),
            'count' => PublicRthStatisticsBuilder::formatCount($value !== null ? (int) $value : null),
            'percent' => PublicRthStatisticsBuilder::formatPercent($value !== null ? (float) $value : null, 2),
            default => (string) ($value ?? '—'),
        };
    };

    $shortLabel = fn (array $row): string => match ($row['key']) {
        'A' => 'Luas dikelola',
        'B' => 'Jumlah lokasi',
        'C' => 'Acuan RTRW',
        'D' => 'Capaian RTRW',
        'E' => 'Luas terpelihara',
        'F' => 'Lokasi terpelihara',
        'G' => 'Rasio terpelihara',
        default => $row['label'],
    };
@endphp

<div class="rth-year-mobile-only mb-4 flex flex-wrap gap-2" role="tablist" aria-label="Pilih tahun capaian">
    @foreach ($rthYearlySummary['years'] as $year)
        <button type="button"
                class="rth-year-tab {{ $year === $latestYear ? 'is-active' : '' }}"
                data-rth-year-tab="{{ $year }}"
                role="tab"
                aria-selected="{{ $year === $latestYear ? 'true' : 'false' }}">
            {{ $year }}
        </button>
    @endforeach
</div>

@foreach ($rthYearlySummary['years'] as $year)
    <div class="rth-year-panel rth-year-mobile-only {{ $year === $latestYear ? 'is-active' : '' }}"
         data-rth-year-panel="{{ $year }}"
         role="tabpanel">
        <dl class="grid gap-3 sm:grid-cols-2">
            @foreach ($rthYearlySummary['rows'] as $row)
                <div class="rounded-xl border border-emerald-100 bg-white px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $row['key'] }} · {{ $shortLabel($row) }}</dt>
                    <dd class="mt-1 text-lg font-black text-gray-900">{{ $formatValue($row, $year) }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
@endforeach

<div class="hidden overflow-x-auto rounded-2xl border border-emerald-100 md:block">
    <table class="min-w-full text-sm">
        <thead class="bg-emerald-50/80">
            <tr>
                <th class="min-w-[12rem] px-4 py-3 text-left font-semibold text-emerald-900">Indikator</th>
                @foreach ($rthYearlySummary['years'] as $year)
                    <th class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-900">{{ $year }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-emerald-50 bg-white">
            @foreach ($rthYearlySummary['rows'] as $row)
                <tr class="hover:bg-emerald-50/40">
                    <td class="px-4 py-3 text-gray-800">
                        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-800">{{ $row['key'] }}</span>
                        {{ $shortLabel($row) }}
                    </td>
                    @foreach ($rthYearlySummary['years'] as $year)
                        <td @class([
                            'whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-800' => $year === $latestYear,
                            'whitespace-nowrap px-4 py-3 text-right text-gray-700' => $year !== $latestYear,
                        ])>
                            {{ $formatValue($row, $year) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
