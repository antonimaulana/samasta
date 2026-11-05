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

    $highlightKeys = ['A', 'B', 'D', 'E', 'G'];
@endphp

<div class="overflow-x-auto rounded-2xl border border-emerald-100 [-webkit-overflow-scrolling:touch]">
    <table class="min-w-full text-sm">
        <thead class="bg-emerald-50/80">
            <tr>
                <th class="min-w-[11rem] px-3 py-3 text-left text-xs font-semibold text-emerald-900 sm:min-w-[16rem] sm:px-4 sm:text-sm">
                    Indikator
                </th>
                @foreach ($rthYearlySummary['years'] as $year)
                    <th class="whitespace-nowrap px-3 py-3 text-right text-xs font-semibold text-emerald-900 sm:px-4 sm:text-sm">
                        {{ $year }}
                        @if ($year === $latestYear)
                            <span class="ml-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">Terbaru</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-emerald-50 bg-white">
            @foreach ($rthYearlySummary['rows'] as $row)
                @php
                    $isHighlight = in_array($row['key'], $highlightKeys, true);
                @endphp
                <tr @class(['hover:bg-emerald-50/40', 'bg-emerald-50/20' => $isHighlight])>
                    <td class="px-3 py-3 align-top sm:px-4">
                        <div class="flex min-w-[10.5rem] items-start gap-2 sm:min-w-[15rem] sm:max-w-md">
                            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-800">
                                {{ $row['key'] }}
                            </span>
                            <span class="min-w-0 flex-1 text-xs leading-snug text-gray-800 [overflow-wrap:anywhere] sm:text-sm sm:leading-normal">
                                {{ $row['label'] }}
                            </span>
                        </div>
                    </td>
                    @foreach ($rthYearlySummary['years'] as $year)
                        <td class="whitespace-nowrap px-3 py-3 text-right align-top text-xs tabular-nums text-gray-700 sm:px-4 sm:text-sm">
                            {{ $formatValue($row, $year) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
