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

{{-- Mobile: kartu per indikator --}}
<div class="space-y-3 sm:hidden">
    @foreach ($rthYearlySummary['rows'] as $row)
        @php
            $isHighlight = in_array($row['key'], $highlightKeys, true);
        @endphp
        <article @class([
            'rounded-xl border border-emerald-100 bg-white p-3 shadow-sm',
            'ring-1 ring-emerald-100' => $isHighlight,
        ])>
            <div class="flex gap-2 text-sm text-gray-800">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-800">
                    {{ $row['key'] }}
                </span>
                <p class="min-w-0 flex-1 leading-snug">{{ $row['label'] }}</p>
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-emerald-50 pt-3">
                @foreach ($rthYearlySummary['years'] as $year)
                    <div class="rounded-lg bg-emerald-50/50 px-2.5 py-2">
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-emerald-800">
                            {{ $year }}
                            @if ($year === $latestYear)
                                <span class="ml-0.5 normal-case text-emerald-600">· terbaru</span>
                            @endif
                        </dt>
                        <dd class="mt-0.5 text-sm font-medium tabular-nums text-gray-900">
                            {{ $formatValue($row, $year) }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </article>
    @endforeach
</div>

{{-- Tablet/desktop: tabel dengan kolom indikator sticky --}}
<div class="relative hidden sm:block">
    <div class="overflow-x-auto rounded-2xl border border-emerald-100 shadow-sm [-webkit-overflow-scrolling:touch]">
        <table class="min-w-[36rem] w-full text-sm">
            <thead class="bg-emerald-50/80">
                <tr>
                    <th class="sticky left-0 z-10 min-w-[12rem] max-w-[14rem] bg-emerald-50/95 px-3 py-3 text-left text-xs font-semibold text-emerald-900 backdrop-blur-sm sm:min-w-[16rem] sm:px-4 sm:text-sm">
                        Indikator
                    </th>
                    @foreach ($rthYearlySummary['years'] as $year)
                        <th class="whitespace-nowrap px-3 py-3 text-right text-xs font-semibold text-emerald-900 sm:px-4 sm:text-sm">
                            {{ $year }}
                            @if ($year === $latestYear)
                                <span class="ml-1 hidden rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white md:inline">Terbaru</span>
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
                        <td @class([
                            'sticky left-0 z-10 min-w-[12rem] max-w-[14rem] px-3 py-2.5 text-xs leading-snug text-gray-800 backdrop-blur-sm sm:min-w-[16rem] sm:px-4 sm:py-3 sm:text-sm',
                            'bg-white/95' => ! $isHighlight,
                            'bg-emerald-50/95' => $isHighlight,
                        ])>
                            <span class="mr-1.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-[10px] font-bold text-emerald-800 sm:mr-2 sm:h-6 sm:w-6 sm:text-xs">
                                {{ $row['key'] }}
                            </span>
                            <span class="align-middle">{{ $row['label'] }}</span>
                        </td>
                        @foreach ($rthYearlySummary['years'] as $year)
                            <td class="whitespace-nowrap px-3 py-2.5 text-right text-xs tabular-nums text-gray-700 sm:px-4 sm:py-3 sm:text-sm">
                                {{ $formatValue($row, $year) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
