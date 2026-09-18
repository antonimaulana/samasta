@php
    use App\Support\PublicRthStatisticsBuilder as RthStats;

    $formatValue = function (array $row, int $year): string {
        $value = $row['values'][$year] ?? null;

        return match ($row['format']) {
            'area', 'count' => RthStats::formatArea((int) $value),
            'percent' => RthStats::formatPercent((float) $value, 2),
            default => (string) $value,
        };
    };
@endphp

<div class="overflow-x-auto rounded-2xl border border-emerald-100">
    <table class="min-w-full text-sm">
        <thead class="bg-emerald-50/80">
            <tr>
                <th class="min-w-[16rem] px-4 py-3 text-left font-semibold text-emerald-900 sm:min-w-[22rem] sm:px-5">Keterangan</th>
                @foreach ($rthYearlySummary['years'] as $year)
                    <th class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-900 sm:px-5">{{ $year }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-emerald-50 bg-white">
            @foreach ($rthYearlySummary['rows'] as $row)
                <tr class="hover:bg-emerald-50/40">
                    <td class="px-4 py-3 text-gray-800 sm:px-5">
                        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-800">{{ $row['key'] }}</span>
                        {{ $row['label'] }}
                    </td>
                    @foreach ($rthYearlySummary['years'] as $year)
                        @php
                            $isHighlight = in_array($row['key'], ['D', 'G'], true) && $year === $latestYear;
                        @endphp
                        <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5 {{ $isHighlight ? 'font-bold text-emerald-800' : 'text-gray-700' }}">
                            {{ $formatValue($row, $year) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
